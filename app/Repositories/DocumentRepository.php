<?php

namespace App\Repositories;

use App\Core\NotificationProcessor;
use App\Models\Department;
use App\Models\Document;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        $userDepartmentId = $user->department_id;
        $groupIds = $user->groups->pluck('id')->toArray();
        $userId = $user->id;

        // Get user's effective scope based on rank
        $scope = $this->getEffectiveScope();
        $departmentScope = $this->getDepartmentScopeForRank($scope, $userDepartmentId);

        Log::info('User Scope: ' . $scope);
        Log::info('Department Scope: ' . json_encode($departmentScope));

        // Single optimized query combining all access algorithms
        $query = $this->instanceOfModel()->with([
            'drafts',
            'uploads',
            'conversations',
            'linkedDocuments.drafts',
            'linkedDocuments.uploads'
        ]);

        $query = $this->applyBudgetYearFilter($query, config('site.jolt_budget_year'));

        // For board users, skip all filtering - they can see everything
        // if ($scope === 'board') {
        //     return $query->latest()->paginate(50);
        // }

        $query->where(function ($q) use ($userId, $userDepartmentId, $groupIds, $departmentScope, $scope) {

            // Algorithm 1: Department-based access (based on user's rank scope)
            if (!empty($departmentScope)) {
                if ($scope === 'personal') {
                    $q->whereIn('user_id', $departmentScope);
                } else {
                    $q->whereIn('department_id', $departmentScope);
                }
            }

            // Algorithm 2: Documents with drafts where user is creator or in group
            $q->orWhereHas('drafts', function ($draftQuery) use ($userId, $groupIds) {
                $draftQuery->where('operator_id', $userId)
                          ->orWhereIn('group_id', $groupIds);
            });

            // Algorithm 3: Documents with conversations where user is thread owner or recipient
            $q->orWhereHas('conversations', function ($conversationQuery) use ($userId) {
                $conversationQuery->where('thread_owner_id', $userId)
                                 ->orWhere('recipient_id', $userId);
            });

            // Algorithm 4: Documents with workflow trackers where user belongs to the group
            $q->orWhereHas('workflow.trackers', function ($trackerQuery) use ($userId, $groupIds) {
                $trackerQuery->where('user_id', $userId)
                            ->orWhereIn('group_id', $groupIds);
            });

            // Algorithm 5: Documents accessible through config workflow (JSON-based access)
            $q->orWhere(function ($configQuery) use ($userId, $groupIds) {
                // Check for user_id in all three objects
                $configQuery->whereRaw('JSON_EXTRACT(config->"$.to.user_id", "$") = ?', [$userId])
                    ->orWhereRaw('JSON_EXTRACT(config->"$.from.user_id", "$") = ?', [$userId])
                    ->orWhereRaw('JSON_EXTRACT(config->"$.through.user_id", "$") = ?', [$userId]);

                // Check for group_id in all three objects
                foreach ($groupIds as $groupId) {
                    $configQuery->orWhereRaw('JSON_EXTRACT(config->"$.to.group_id", "$") = ?', [$groupId])
                        ->orWhereRaw('JSON_EXTRACT(config->"$.from.group_id", "$") = ?', [$groupId])
                        ->orWhereRaw('JSON_EXTRACT(config->"$.through.group_id", "$") = ?', [$groupId]);
                }
            });
        });

        return $query->latest()->paginate(50);
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
