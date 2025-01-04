<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\DocumentRequirementRepository;
use App\Repositories\GroupRepository;
use App\Repositories\WorkflowStageRepository;
use Illuminate\Support\Facades\DB;

class WorkflowStageService extends BaseService
{
    protected DocumentActionRepository $documentActionRepository;
    protected DocumentRequirementRepository $documentRequirementRepository;
    protected GroupRepository $groupRepository;
    public function __construct(
        WorkflowStageRepository $workflowStageRepository,
        DocumentActionRepository $documentActionRepository,
        DocumentRequirementRepository $documentRequirementRepository,
        GroupRepository $groupRepository
    ) {
        parent::__construct($workflowStageRepository);
        $this->documentActionRepository = $documentActionRepository;
        $this->documentRequirementRepository = $documentRequirementRepository;
        $this->groupRepository = $groupRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_stage_category_id' => 'required|integer|exists:workflow_stage_categories,id',
            'assistant_group_id' => 'sometimes|integer|min:0',
            'fallback_stage_id' => 'sometimes|integer|min:0',
            'group_id' => 'required|integer|exists:groups,id',
            'department_id' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'selectedActions' => 'required|array',
            'selectedDocumentsRequired' => 'nullable|sometimes|array',
            'recipients' => 'required|array',
            'flag' => 'required|string|in:pass,stall,fail',
            'alert_recipients' => 'required',
            'supporting_documents_verified' => 'required|boolean',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $stage = parent::store($data);

            if ($stage) {
                foreach ($data['selectedActions'] as $obj) {
                    $action = $this->documentActionRepository->find($obj['value']);

                    if ($action && !in_array($action->id, $stage->actions->pluck('id')->toArray())) {
                        $stage->actions()->save($action);
                    }
                }

                if (isset($data['selectedDocumentsRequired'])) {
                    foreach ($data['selectedDocumentsRequired'] as $obj) {
                        $documentRequirement = $this->documentRequirementRepository->find($obj['value']);

                        if ($documentRequirement && !in_array($documentRequirement->id, $stage->requirements->pluck('id')->toArray())) {
                            $stage->requirements()->save($documentRequirement);
                        }
                    }
                }

                if (isset($data['recipients'])) {
                    foreach ($data['recipients'] as $obj) {
                        $recipient = $this->groupRepository->find($obj['value']);

                        if ($recipient && !in_array($recipient->id, $stage->recipients->pluck('id')->toArray())) {
                            $stage->recipients()->save($recipient);
                        }
                    }
                }
            }

            return $stage;
        });
    }
}
