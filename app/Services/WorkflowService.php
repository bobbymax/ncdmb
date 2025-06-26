<?php

namespace App\Services;

use App\Handlers\DataNotFound;
use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\WidgetRepository;
use App\Repositories\WorkflowRepository;
use App\Traits\ServiceAction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService extends BaseService
{
    use ServiceAction;
    protected DocumentActionRepository $documentActionRepository;
    protected MailingListRepository $mailingListRepository;
    protected ProgressTrackerRepository $progressTrackerRepository;
    protected WidgetRepository $widgetRepository;
    public function __construct(
        WorkflowRepository $workflowRepository,
        DocumentActionRepository $documentActionRepository,
        MailingListRepository $mailingListRepository,
        ProgressTrackerRepository $progressTrackerRepository,
        WidgetRepository $widgetRepository
    ) {
        parent::__construct($workflowRepository);
        $this->documentActionRepository = $documentActionRepository;
        $this->mailingListRepository = $mailingListRepository;
        $this->progressTrackerRepository = $progressTrackerRepository;
        $this->widgetRepository = $widgetRepository;
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
            'stages.*.widgets' => ['sometimes', 'array'],
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
        $this->updateWidgets($tracker, $stage['widgets'] ?? []);

        if (!empty($toDelete)) {
            $this->removeTrackers($toDelete);
        }

        return $tracker;
    }

    private function processStageTrackers(array $stages, $workflow, array $toDelete): void
    {
        collect($stages)->map(function ($stage) use ($workflow, $toDelete) {
            return $this->populateTrackers($stage, $toDelete, $workflow);
        })->first();
    }

    private function getTrackersToDelete($workflow, array $frontendStages): array
    {
        $frontendIdentifiers = array_column($frontendStages, 'identifier');
        $backendIdentifiers = array_map('strval', $this->getTrackerIdentifiers($workflow));

        return array_diff($backendIdentifiers, $frontendIdentifiers);
    }

    public function updateWidgets(mixed $tracker, array $widgetObjs): void
    {
        $widgetIds = array_values(array_filter(array_column($widgetObjs, 'value'), fn($id) => $id !== 0));

//        if (empty($widgetIds)) {
//            $existingWidgetIds = $this->isolateCollectionKeys($tracker->widgets, 'id');
//            if (!empty($existingWidgetIds)) {
//                $this->nullifyWidget($tracker, $existingWidgetIds);
//            }
//
//            return; // No widgets to update, exit early
//        }

//        $widgets = $this->widgetRepository->whereIn('id', $widgetIds);

        $tracker->widgets()->sync($widgetIds);

//        foreach ($widgets as $widget) {
//            $widget->update(['progress_tracker_id' => $tracker->id]);
//        }
    }

    public function nullifyWidget($tracker, array $toNullify): void
    {
        if (empty($toNullify)) {
            return;
        }

        $tracker->widgets()->detach($toNullify);

//        $widgets = $this->widgetRepository->whereIn('id', $toNullify);
//
//        foreach ($widgets as $widget) {
//            $widget->update(['progress_tracker_id' => 0]);
//        }
    }

    public function removeTrackers($toDelete): void
    {
        DB::transaction(function () use ($toDelete) {
            $this->progressTrackerRepository
                ->whereInQuery('identifier', $toDelete)
                ->chunkById(100, function ($chunk) {
                    foreach ($chunk as $model) {
                        // Detach all relations cleanly
                        $model->actions()->detach();
                        $model->recipients()->detach();

                        // Update related widgets safely
                        $model->widgets()->detach();

                        // Delete the model (triggers model events like `deleting`)
                        $model->delete();
                    }
                });
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
