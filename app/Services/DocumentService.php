<?php

namespace App\Services;

use App\Models\Document;
use App\Repositories\DepartmentRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\SignatoryRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use App\Repositories\WorkflowRepository;
use App\Repositories\WorkflowStageRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentService extends BaseService
{
    protected DepartmentRepository $departmentRepository;
    protected ProgressTrackerRepository $progressTrackerRepository;
    protected WorkflowRepository $workflowRepository;
    protected UserRepository $userRepository;
    protected SignatoryRepository $signatoryRepository;
    protected WorkflowStageRepository $workflowStageRepository;
    protected UploadRepository $uploadRepository;
    public function __construct(
        DocumentRepository $documentRepository,
        DepartmentRepository $departmentRepository,
        ProgressTrackerRepository $progressTrackerRepository,
        WorkflowRepository $workflowRepository,
        UserRepository $userRepository,
        SignatoryRepository $signatoryRepository,
        WorkflowStageRepository $workflowStageRepository,
        UploadRepository $uploadRepository
    ) {
        parent::__construct($documentRepository);
        $this->departmentRepository = $departmentRepository;
        $this->progressTrackerRepository = $progressTrackerRepository;
        $this->workflowRepository = $workflowRepository;
        $this->userRepository = $userRepository;
        $this->signatoryRepository = $signatoryRepository;
        $this->workflowStageRepository = $workflowStageRepository;
        $this->uploadRepository = $uploadRepository;
    }

    public function rules($action = 'store'): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'vendor_id' => 'sometimes|integer',
            'documentable_id' => 'required|integer',
            'documentable_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ref' => 'required|string|max:255',
            'file_path_blob' => 'sometimes|nullable|mimes:pdf|max:4096',
            'status' => 'required|string|in:pending,approved,rejected',
            'is_archived' => 'sometimes|boolean',
        ];
    }

    public function collateDocumentsByStatus(string $status, string $scope): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getCollectionByColumn('status', $status, '=', $scope);
    }

    public function generateDocument(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->validateSupplementaryKeys($data);

            $isTrackerPresent = $this->checkTrackersSequence($data['trackers']);

            $resource = processor()->saveResource($data, false);
            $document = $this->createWorkflowAndDocument($isTrackerPresent, $data, $resource);

            $this->processUploads($data['uploads'], $document->id);

            return $document;
        });
    }

    public function processUploads(array $files, int $documentId): void
    {
        $this->uploadRepository->uploadMany(
            $files,
            $documentId,
            Document::class
        );
    }

    private function checkTrackersSequence(array $trackers): array
    {
        $keyColumns = ['carder_id', 'department_id', 'group_id', 'document_type_id', 'workflow_stage_id'];

        $normalizedIncoming = collect($trackers)->map(fn($tracker) => Arr::only($tracker, $keyColumns))->values();

        $matches = $this->progressTrackerRepository->whereInQuery('order', range(1, count($normalizedIncoming)))
            ->where(function ($query) use ($normalizedIncoming) {
                foreach ($normalizedIncoming as $tracker) {
                    $query->orWhere(function ($q) use ($tracker) {
                        foreach ($tracker as $column => $value) {
                            $q->where($column, $value);
                        }
                    });
                }
            })
            ->orderBy('order')
            ->get();

        $matchedNormalized = $matches->map(fn($dbTracker) => Arr::only($dbTracker->toArray(), $keyColumns))->values();

        return [
            'exact_match' => $normalizedIncoming->toArray() === $matchedNormalized->toArray(),
            'workflow_id' => $matches->isNotEmpty() ? $matches->first()->workflow_id : null,
            'db_matches' => $matchedNormalized,
            'incoming' => $normalizedIncoming
        ];
    }

    private function getApprovals(array $contents): array
    {
        return collect($contents)
            ->filter(fn($content) => data_get($content, 'content.approval.approvals'))
            ->pluck('content.approval.approvals')
            ->flatten(1)
            ->toArray();
    }

    private function createWorkflowAndDocument($isTrackerPresent, array $data, mixed $resource): ?object
    {
        $workflow = $isTrackerPresent['exact_match']
            ? $this->workflowRepository->find($isTrackerPresent['workflow_id'])
            : $this->workflowRepository->create([
                'user_id' => Auth::id(),
                'name' => Str::uuid()->toString(),
                'description' => "Automated workflow for {$data['service']} service, generated on " . Carbon::now()->toDateTimeString(),
                'type' => 'serialize',
                'state' => 'custom'
            ]);

        if (!$workflow) return null;

        $document = parent::store([
            'workflow_id' => $workflow->id,
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'progress_tracker_id' => 0,
            'document_category_id' => $data['category']['id'],
            'document_type_id' => $data['category']['document_type_id'],
            'vendor_id' => $data['vendor_id'] ?? 0,
            'document_reference_id' => $data['document_reference_id'] ?? 0,
            'document_action_id' => $data['category']['document_action_id'] ?? 0,
            'fund_id' => $data['fund_id'] ?? null,
            'threshold_id' => $data['threshold_id'] ?? null,
            'title' => $data['document']['title'],
            'documentable_id' => $resource->id,
            'documentable_type' => get_class($resource),
            'ref' => $this->repository->generateRef($data['owner_department_id'], $resource->code ?? 'XXXX'),
            'description' => 'Description is here',
            'config' => json_encode($data['configState']),
            'contents' => json_encode(array_filter($data['contents'], fn($val) => !is_null($val) && $val !== '')),
            'status' => 'pending'
        ]);

        if (!$document) return null;

        foreach ($this->getApprovals($data['contents']) as $approval) {
            $this->createSignatoryAndTracker(
                $approval,
                $workflow->id,
                $document->id,
                $data['page']['id'],
                $data['category']['document_type_id']
            );
        }

        return $document;
    }

    private function createSignatoryAndTracker(
        array $approval,
        int $workflowId,
        int $documentId,
        int $pageId,
        int $documentTypeId
    ): void {
        $user = $this->userRepository->find($approval['approver']['value']);
        if (!$user) return;

        $signatory = $this->signatoryRepository->create([
            'page_id' => $pageId,
            'user_id' => $user->id,
            'group_id' => $approval['group']['value'],
            'department_id' => $approval['department']['value'] ?? 0,
            'document_id' => $documentId,
            'type' => $approval['approval_type'],
            'order' => $approval['order'],
        ]);

        if (!$signatory) return;

        $stageId = $approval['meta_data']['stage']['value'] ?? null;
        $stage = $this->workflowStageRepository->find($stageId);
        if (!$stage || !isset($approval['group']['value'])) return;

        $tracker = $this->progressTrackerRepository->create([
            'workflow_id' => $workflowId,
            'workflow_stage_id' => $stage->id,
            'group_id' => $approval['group']['value'],
            'department_id' => $approval['department']['value'] ?? 0,
            'carder_id' => $approval['approval_type'] !== 'initiator' ? ($user->gradeLevel->carder_id ?? 0) : 0,
            'signatory_id' => $signatory->id,
            'document_type_id' => $documentTypeId,
            'order' => $approval['order'],
            'permission' => $approval['meta_data']['permissions'],
            'identifier' => Str::uuid()->toString(),
        ]);

        if ($tracker) {
            $tracker->actions()->sync($stage->actions->pluck('id')->toArray());
            $tracker->recipients()->sync($stage->recipients->pluck('id')->toArray());
        }
    }

    private function validateSupplementaryKeys(array $data): void
    {
        foreach (['trackers', 'contents', 'configState', 'workflow', 'category', 'service', 'template_id', 'document_owner', 'department_owner', 'page'] as $key) {
            if (data_get($data, $key) === null) {
                throw ValidationException::withMessages([
                    $key => ["Missing required field: {$key}"]
                ]);
            }
        }
    }
}
