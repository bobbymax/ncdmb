<?php

namespace App\Repositories;

use App\Core\NotificationProcessor;
use App\Models\Department;
use App\Models\Document;
use App\Models\ProgressTracker;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentRepository extends BaseRepository
{
    protected Department $department;
    protected Workflow $workflow;
    protected UploadRepository $uploadRepository;
    public function __construct(Document $document, Department $department, Workflow $workflow, UploadRepository $uploadRepository) {
        parent::__construct($document);
        $this->department = $department;
        $this->workflow = $workflow;
        $this->uploadRepository = $uploadRepository;
    }

    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Generate a reference for the document
     *
     * @param int $departmentId
     * @param string $code
     * @param string $prefix
     * @return string
     */
    public function generateRef(int $departmentId, string $code, string $prefix = "AED"): string
    {
        $currentDate = Carbon::now();
        $department = $this->department->find($departmentId);

        if (!$department) {
            return ""; // Handle null department gracefully
        }

        // Construct department hierarchy reference
        $departmentHierarchy = $this->buildDepartmentHierarchy($department);

        // Build reference
        return sprintf(
            "%s%s/%d/%d/%s",
            $departmentHierarchy,
            $prefix,
            $currentDate->year,
            $currentDate->month,
            $code
        );
    }

    public function all()
    {
        $user = Auth::user();
        $userId = $user->id;
        $userDepartmentId = $user->department_id;
        $groupIds = $user->groups->pluck('id')->toArray();

        // Cache scope calculation for 5 minutes
        $scope = Cache::remember("user_scope_{$userId}", 300, function () {
            return $this->getEffectiveScope();
        });

        $departmentScope = $this->getDepartmentScopeForRank($scope, $userDepartmentId);

        Log::info('DocumentRepository: Fetching documents for user', [
            'user_id' => $userId,
            'scope' => $scope,
            'department_scope' => $departmentScope,
            'group_count' => count($groupIds)
        ]);

        // Base query with optimized eager loading
        $query = $this->instanceOfModel()->with([
            'current_tracker',
            'drafts' => function ($q) {
                $q->latest()->limit(5);
            },
            'uploads',
            'conversations',
            'linkedDocuments.drafts',
            'linkedDocuments.uploads'
        ]);

        // Apply budget year filter
        $query = $this->applyBudgetYearFilter($query, config('site.jolt_budget_year'));

        // Board users see everything - early return for performance
        if ($scope === 'board') {
            Log::info('DocumentRepository: Board user - returning all documents');
            return $query->latest()->paginate(config('pagination.documents_per_page', 50));
        }

        // Cache tracker IDs for 1 minute (they change rarely)
        $currentTrackerIds = Cache::remember("user_current_trackers_{$userId}", 60, function () use ($userId, $groupIds, $userDepartmentId) {
            return $this->getUserCurrentTrackerIds($userId, $groupIds, $userDepartmentId);
        });

        $allTrackerIds = Cache::remember("user_all_trackers_{$userId}", 60, function () use ($userId, $groupIds) {
            return $this->getUserAllTrackerIds($userId, $groupIds);
        });

        // Build access query with prioritized algorithms
        $query->where(function ($q) use (
            $userId,
            $userDepartmentId,
            $groupIds,
            $departmentScope,
            $scope,
            $currentTrackerIds,
            $allTrackerIds
        ) {
            // PRIORITY 1: Documents currently awaiting user's action (HIGHEST PRIORITY)
            if (!empty($currentTrackerIds)) {
                $q->whereIn('progress_tracker_id', $currentTrackerIds);
            }

            // PRIORITY 2: Department-based access (based on user's rank scope)
            if (!empty($departmentScope)) {
                $q->orWhere(function ($deptQuery) use ($departmentScope, $scope, $userId) {
                    if ($scope === 'personal') {
                        // Personal scope: only documents created by user
                        $deptQuery->where('user_id', $userId);
                    } else {
                        // Departmental/Directorate: documents in department scope
                        $deptQuery->whereIn('department_id', $departmentScope);
                    }
                });
            }

            // PRIORITY 3: Documents where user is the owner/creator
            $q->orWhere('user_id', $userId);

            // PRIORITY 4: Documents with active drafts in user's groups
            $q->orWhereHas('drafts', function ($draftQuery) use ($userId, $groupIds) {
                $draftQuery->where(function ($active) use ($userId, $groupIds) {
                    $active->whereIn('status', ['pending', 'attention', 'awaiting'])
                          ->where(function ($userOrGroup) use ($userId, $groupIds) {
                              $userOrGroup->where('operator_id', $userId)
                                         ->orWhereIn('group_id', $groupIds);
                          });
                });
            });

            // PRIORITY 5: Documents with active conversations
            $q->orWhereHas('conversations', function ($conversationQuery) use ($userId) {
                $conversationQuery->where('thread_owner_id', $userId)
                                 ->orWhere('recipient_id', $userId);
            });

            // PRIORITY 6: Documents in workflows user participates in (historical context)
            if (!empty($allTrackerIds) && !empty($currentTrackerIds)) {
                $q->orWhere(function ($workflowQuery) use ($allTrackerIds, $currentTrackerIds) {
                    $workflowQuery->whereHas('workflow.trackers', function ($trackerQuery) use ($allTrackerIds) {
                        $trackerQuery->whereIn('id', $allTrackerIds);
                    })
                    // Exclude documents already matched by current tracker
                    ->whereNotIn('progress_tracker_id', $currentTrackerIds);
                });
            }

            // PRIORITY 7: Config-based access (legacy/special cases)
            if (!empty($groupIds)) {
                $q->orWhere(function ($configQuery) use ($userId, $groupIds) {
                    // User-specific config access
                    $configQuery->whereRaw('JSON_CONTAINS(config, ?, "$.to.user_id")', [json_encode($userId)])
                        ->orWhereRaw('JSON_CONTAINS(config, ?, "$.from.user_id")', [json_encode($userId)])
                        ->orWhereRaw('JSON_CONTAINS(config, ?, "$.through.user_id")', [json_encode($userId)]);

                    // Group-specific config access
                    $groupIdsJson = json_encode($groupIds);
                    $configQuery->orWhereRaw('JSON_OVERLAPS(JSON_EXTRACT(config, "$.to.group_id"), CAST(? AS JSON))', [$groupIdsJson])
                        ->orWhereRaw('JSON_OVERLAPS(JSON_EXTRACT(config, "$.from.group_id"), CAST(? AS JSON))', [$groupIdsJson])
                        ->orWhereRaw('JSON_OVERLAPS(JSON_EXTRACT(config, "$.through.group_id"), CAST(? AS JSON))', [$groupIdsJson]);
                });
            }
        });

        // Order by priority: current tracker docs first, then by latest
        if (!empty($currentTrackerIds)) {
            $trackerIdsString = implode(',', $currentTrackerIds);
            $query->orderByRaw("CASE WHEN progress_tracker_id IN ({$trackerIdsString}) THEN 0 ELSE 1 END")
                  ->latest();
        } else {
            $query->latest();
        }

        return $query->paginate(config('pagination.documents_per_page', 50));
    }

    /**
     * Get tracker IDs where user is currently responsible
     * Note: Trackers are assigned to groups/departments, not directly to users
     */
    private function getUserCurrentTrackerIds(int $userId, array $groupIds, int $departmentId): array
    {
        if (empty($groupIds) && !$departmentId) {
            return [];
        }

        return ProgressTracker::query()
            ->where(function ($q) use ($groupIds, $departmentId) {
                // Trackers assigned to user's groups
                if (!empty($groupIds)) {
                    $q->whereIn('group_id', $groupIds);
                }
                
                // Trackers assigned to user's department
                if ($departmentId > 0) {
                    $q->orWhere('department_id', $departmentId);
                }
                
                // Trackers with both department and group match (highest priority)
                if ($departmentId > 0 && !empty($groupIds)) {
                    $q->orWhere(function ($deptGroup) use ($departmentId, $groupIds) {
                        $deptGroup->where('department_id', $departmentId)
                                 ->whereIn('group_id', $groupIds);
                    });
                }
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get all tracker IDs where user has ever been/will be involved
     * Note: Based on group membership only, since trackers don't have user_id
     */
    private function getUserAllTrackerIds(int $userId, array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }

        return ProgressTracker::query()
            ->whereIn('group_id', $groupIds)
            ->pluck('id')
            ->toArray();
    }

    public function processSupportingDocuments(array $files, int $documentId): void
    {
        $this->uploadRepository->uploadMany(
            $files,
            $documentId,
            Document::class
        );
    }

    public function linkRelatedDocument(
        Document $document,
        Document $linked,
        string $type,
        string $status = "processing"
    ): void {
        $document->linkedDocuments()->attach($linked->id, [
            'relationship_type' => $type,
        ]);

        $linked->update([
            'status' => $status
        ]);
    }

    public function build(
        array $data,
        object $resource,
        int $departmentId,
        string $title,
        string $description,
        bool $triggerWorkflow = false,
        ?int $triggeredWorkflowId = null,
        ?array $args = [],
        ?array $supportingDocuments = [],
        ?string $column = "code",
    ): array {

        $workflowId  = $triggeredWorkflowId && $triggeredWorkflowId > 1 ? $triggeredWorkflowId : $data['workflow_id'];

        return [
            'user_id' => Auth::id(),
            'workflow_id' => $workflowId,
            'department_id' => $departmentId,
            'document_category_id' => $data['document_category_id'],
            'document_reference_id' => $data['document_reference_id'] ?? 0,
            'document_type_id' => $data['document_type_id'],
            'document_action_id' => $data['document_action_id'] ?? 0,
            'progress_tracker_id' => $this->getFirstTrackerId($workflowId),
            'vendor_id' => $data['vendor_id'] ?? 0,
            'title' => $title,
            'description' => $description,
            'documentable_id' => $resource->id,
            'documentable_type' => get_class($resource),
            'relationship_type' => $data['relationship_type'] ?? null,
            'linked_document' => $data['linked_document'] ?? null,
            'ref' => $this->generateRef($departmentId, $resource[$column]),
            'args' => $args,
            'trigger_workflow' => $triggerWorkflow,
            'supporting_documents' => $supportingDocuments,
        ];
    }

    protected function getFirstTrackerId(int $workflowId)
    {
        $workflow = $this->workflow->find($workflowId);

        if (!$workflow) {
            return 0;
        }
        $tracker = $workflow->trackers()->where('order', 1)->first();

        return $tracker?->id ?? 0;
    }


    /**
     * Build the hierarchical department reference
     *
     * @param Department $department
     * @return string
     */
    private function buildDepartmentHierarchy(Department $department): string
    {
        $hierarchy = [];

        if ($department->type === 'directorate') {
            $hierarchy[] = $department->abv;
        } elseif ($department->type === 'division') {
            $directorate = $department->parent;
            if ($directorate) {
                $hierarchy[] = $directorate->abv;
            }
            $hierarchy[] = $department->abv;
        } else {
            $division = $department->parent;
            $directorate = $division?->parent;

            if ($directorate) {
                $hierarchy[] = $directorate->abv;
            }
            if ($division) {
                $hierarchy[] = $division->abv;
            }
            $hierarchy[] = $department->abv;
        }

        return implode("/", $hierarchy) . "/";
    }

    /**
     * Get department scope based on user's rank
     *
     * @param string $rank
     * @param int $userDepartmentId
     * @return array
     */
    private function getDepartmentScopeForRank(string $rank, int $userDepartmentId): array
    {
        $userDepartment = Department::find($userDepartmentId);
        if (!$userDepartment) {
            return [];
        }

        return match ($rank) {
            'board' => [], // No restriction - can see all documents
            'directorate' => $this->getDirectorateScope($userDepartment),
            'departmental' => [$userDepartmentId],
            'personal' => [Auth::id()], // Only own department
            default => [$userDepartmentId],
        };
    }

    /**
     * Get directorate scope (all departments under the directorate)
     *
     * @param Department $userDepartment
     * @return array
     */
    private function getDirectorateScope(Department $userDepartment): array
    {
        // If user is in a directorate, get all departments under it
        if ($userDepartment->type === 'directorate') {
            return Department::where('id', $userDepartment->id)
                ->orWhere('parentId', $userDepartment->id)
                ->pluck('id')
                ->toArray();
        }

        // If user is in a division or department, find the parent directorate
        $directorate = Department::where('id', function ($query) use ($userDepartment) {
            $query->select('parentId')
                  ->from('departments')
                  ->where('id', $userDepartment->parentId)
                  ->where('type', 'division');
        })->first();

        return $directorate ? Department::where('id', $directorate->id)
            ->orWhere('parentId', $directorate->id)
            ->pluck('id')
            ->toArray() : [$userDepartment->id];
    }
}
