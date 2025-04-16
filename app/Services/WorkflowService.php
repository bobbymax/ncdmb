<?php

namespace App\Services;

use App\Handlers\DataNotFound;
use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\WorkflowRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService extends BaseService
{
    protected DocumentActionRepository $documentActionRepository;
    protected MailingListRepository $mailingListRepository;
    protected ProgressTrackerRepository $progressTrackerRepository;
    public function __construct(
        WorkflowRepository $workflowRepository,
        DocumentActionRepository $documentActionRepository,
        MailingListRepository $mailingListRepository,
        ProgressTrackerRepository $progressTrackerRepository
    ) {
        parent::__construct($workflowRepository);
        $this->documentActionRepository = $documentActionRepository;
        $this->mailingListRepository = $mailingListRepository;
        $this->progressTrackerRepository = $progressTrackerRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'type' => 'sometimes|string|in:serialize,broadcast',
            'stages' => ['sometimes', 'nullable', 'array'],
            'stages.*.identifier' => ['required', 'string', 'max:36'],
            'stages.*.workflow_stage_id' => ['required', 'integer', 'exists:workflow_stages,id'],
            'stages.*.internal_process_id' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'stages.*.group_id' => ['required', 'integer', 'min:1', 'exists:groups,id'],
            'stages.*.department_id' => $this->departmentValidationRule(),
            'stages.*.carder_id' => ['required', 'integer', 'min:1', 'exists:carders,id'],
            'stages.*.document_type_id' => ['required', 'integer', 'min:1', 'exists:document_types,id'],
            'stages.*.order' => ['required', 'integer', 'between:1,100'],
            'stages.*.actions' => ['required', 'array'],
            'stages.*.recipients' => ['required', 'array'],
            'stages.*.signatory_id' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Handles custom validation for department ID.
     *
     * @return array
     */
    private function departmentValidationRule(): array
    {
        return [
            'sometimes',
            'integer',
            'min:0',
            function ($attribute, $value, $fail) {
                if ($value > 0 && !DB::table('departments')->where('id', $value)->exists()) {
                    $fail('The selected department does not exist in our database.');
                }
            }
        ];
    }

    private function getTrackerIdentifiers($workflow)
    {
        return $workflow->trackers->pluck('identifier')->toArray();
    }

    /**
     * Create a new progress tracker record.
     *
     * @param int $workflowId
     * @param array $stage
     * @return mixed
     * @throws \Exception
     */
    private function createProgressTracker(int $workflowId, array $stage): mixed
    {
        return $this->progressTrackerRepository->create([
            'workflow_id' => $workflowId,
            ...$stage,
        ]);
    }

    /**
     * Attach actions to the progress tracker.
     *
     * @param mixed $progressLine
     * @param array $recipients
     * @return void
     */
    private function attachRecipients(mixed $progressLine, array $recipients): void
    {
        if (!empty($recipients)) {
            $ids = array_column($recipients, 'value');
            $progressLine->recipients()->sync($ids);
        }
    }

    /**
     * Attach actions to the progress tracker.
     *
     * @param mixed $progressLine
     * @param array $actions
     * @return void
     */
    private function attachActions(mixed $progressLine, array $actions): void
    {
        if (!empty($actions)) {
            $ids = array_column($actions, 'value');
            $progressLine->actions()->sync($ids);
        }
    }

    /**
     * @throws DataNotFound
     * @throws Exception
     */
    public function populateTrackers($stage, $toDelete, $workflow)
    {
        $tracker = $this->progressTrackerRepository->getRecordByColumn('identifier', $stage['identifier']);
        unset($stage['id']);

        if ($tracker) {
            $this->progressTrackerRepository->update($tracker->id, $stage);
        } else {
            $tracker = $this->createProgressTracker($workflow->id, $stage);
        }

        $this->attachActions($tracker, $stage['actions'] ?? []);
        $this->attachRecipients($tracker, $stage['recipients'] ?? []);

        if (!empty($toDelete)) {
            $this->removeTrackers($toDelete);
        }

        return $tracker;
    }

    private function processStageTrackers(array $stages, $workflow, array $toDelete)
    {
        return collect($stages)->map(function ($stage) use ($workflow, $toDelete) {
            return $this->populateTrackers($stage, $toDelete, $workflow);
        })->first();
    }

    private function getTrackersToDelete($workflow, array $frontendStages): array
    {
        $frontendIdentifiers = array_column($frontendStages, 'identifier');
        $backendIdentifiers = array_map('strval', $this->getTrackerIdentifiers($workflow));

        return array_diff($backendIdentifiers, $frontendIdentifiers);
    }

    public function removeTrackers($toDelete): void
    {
        $this->progressTrackerRepository->whereInQuery('identifier', $toDelete)->chunkById(100, function ($chunk) {
            foreach ($chunk as $model) {
                $model->delete(); // triggers the `deleting` event above
            }
        });
    }

    private function updateWorkflow(int $id, array $data)
    {
        $workflow = $this->repository->find($id);
        if (!$workflow) return null;

        $updated = [
            'name' => $data['name'] ?? $workflow->name,
            'description' => $data['description'] ?? $workflow->description,
            'type' => $data['type'] ?? $workflow->type,
        ];

        $workflow->update($updated);
        return $workflow;
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            $workflow = $this->updateWorkflow($id, $data);

            if (!$workflow) {
                return null;
            }

            if (isset($data['stages'])) {
                $toDelete = $this->getTrackersToDelete($workflow, $data['stages']);
                $this->processStageTrackers($data['stages'], $workflow, $toDelete);
            }

            return $workflow;
        });
    }
}
