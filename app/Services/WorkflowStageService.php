<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\WorkflowStageRepository;
use Illuminate\Support\Facades\DB;

class WorkflowStageService extends BaseService
{
    protected GroupRepository $groupRepository;
    protected DocumentActionRepository $documentActionRepository;
    protected MailingListRepository $mailingListRepository;
    public function __construct(
        WorkflowStageRepository $workflowStageRepository,
        GroupRepository $groupRepository,
        DocumentActionRepository $documentActionRepository,
        MailingListRepository $mailingListRepository
    ) {
        parent::__construct($workflowStageRepository);
        $this->groupRepository = $groupRepository;
        $this->documentActionRepository = $documentActionRepository;
        $this->mailingListRepository = $mailingListRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_stage_category_id' => 'required|integer|exists:workflow_stage_categories,id',
            'department_id' => 'required|integer|min:0',
            'fallback_stage_id' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'category' => 'nullable|sometimes|string|in:staff,third-party,system',
            'can_appeal' => 'nullable|boolean',
            'append_signature' => 'nullable|boolean',
            'groups' => 'required|array',
            'actions' => 'required|array',
            'recipients' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflowStage = parent::store($data);
            if (!$workflowStage) {
                return null;
            }

            // Sync relationships
            $this->syncRelationships($workflowStage, $data);

            return $workflowStage;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            $workflowStage = parent::update($id, $data, $parsed);
            if (!$workflowStage) {
                return null;
            }

            // Sync relationships
            $this->syncRelationships($workflowStage, $data);

            return $workflowStage;
        });
    }

    private function syncRelationships($workflowStage, array $data): void
    {
        // Sync Groups
        if (!empty($data['groups'])) {
            $groupIds = collect($data['groups'])->pluck('value')->toArray();
            $workflowStage->groups()->sync($groupIds);
        }

        // Sync Actions
        if (!empty($data['actions'])) {
            $actionIds = collect($data['actions'])->pluck('value')->toArray();
            $workflowStage->actions()->sync($actionIds);
        }

        // Sync Recipients
        if (!empty($data['recipients'])) {
            $recipientIds = collect($data['recipients'])->pluck('value')->toArray();
            $workflowStage->recipients()->sync($recipientIds);
        }
    }
}
