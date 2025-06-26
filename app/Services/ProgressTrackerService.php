<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\WidgetRepository;
use App\Repositories\WorkflowRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgressTrackerService extends BaseService
{
    protected DocumentActionRepository $documentActionRepository;
    protected MailingListRepository $mailingListRepository;
    protected WorkflowRepository $workflowRepository;
    protected WidgetRepository $widgetRepository;

    public function __construct(
        ProgressTrackerRepository $progressTrackerRepository,
        DocumentActionRepository $documentActionRepository,
        MailingListRepository $mailingListRepository,
        WorkflowRepository $workflowRepository,
        WidgetRepository $widgetRepository
    ) {
        parent::__construct($progressTrackerRepository);
        $this->documentActionRepository = $documentActionRepository;
        $this->mailingListRepository = $mailingListRepository;
        $this->workflowRepository = $workflowRepository;
        $this->widgetRepository = $widgetRepository;
    }

    /**
     * Define validation rules for storing/updating a progress tracker.
     *
     * @param string $action
     * @return array
     */
    public function rules($action = "store"): array
    {
        return [
            'workflow_id' => ['required', 'integer', 'exists:workflows,id'],
            'stages' => ['required', 'array'],
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
            'stages.*.widgets' => ['required', 'array'],
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

    /**
     * Store a progress tracker record along with actions and recipients.
     *
     * @param array $data
     * @return mixed
     */
    public function store(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['stages'])) {
                return null;
            }

            return collect($data['stages'])->map(function ($stage) use ($data) {
                return $this->handleTrackerAttachments($data['workflow_id'], $stage);
            })->first();
        });
    }

    /**
     * @throws \Exception
     */
    private function handleTrackerAttachments(int $workflowId, $stage)
    {
        $tracker = $this->createProgressTracker($workflowId, $stage);

        if (!$tracker) {
            return null;
        }

        $this->attachWidgets($tracker, $stage['widgets'] ?? []);
        $this->attachRecipients($tracker, $stage['recipients'] ?? []);
        $this->attachActions($tracker, $stage['actions'] ?? []);

        return $tracker;
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $parsed, $data) {
            $workflow = $this->workflowRepository->find($id);

            if (!$workflow) {
                return null;
            }

            $fromFrontendTrackerIds = array_column(json_decode(json_encode($data['stages']), true), 'identifier');
            $backendTrackerIds = array_map('strval', $this->getTrackerIds($workflow));

            // Find IDs that exist in the backend but NOT in the frontend (to delete)
            $toDelete = array_diff($backendTrackerIds, $fromFrontendTrackerIds);

            foreach ($data['stages'] as $stage) {
                $this->handleTrackerUpdate($stage, $toDelete, $workflow);
            }

            return $workflow->trackers()->first();
        });
    }

    private function getTrackerIds($workflow)
    {
        return $workflow->trackers->pluck('identifier')->toArray();
    }

    /**
     * @throws \Exception
     */
    private function handleTrackerUpdate(array $stage, array $toDelete, $workflow): void
    {
        $tracker = $this->repository->getRecordByColumn('identifier', $stage['identifier']);

        unset($stage['id']);

        if ($tracker) {
            $tracker->update($stage);

            if (!in_array($tracker->identifier, $toDelete)) {
                $this->attachWidgets($tracker, $stage['widgets'] ?? [], true);
                $this->attachRecipients($tracker, $stage['recipients'] ?? [], true);
                $this->attachActions($tracker, $stage['actions'] ?? [], true);
            } else {
                $this->detachWidgets($tracker, $stage['widgets'] ?? []);
                $this->detachRecipients($tracker, $stage['recipients'] ?? []);
                $this->detachActions($tracker, $stage['actions'] ?? []);
            }
        } else {
            $this->handleTrackerAttachments($workflow->id, $stage);
        }
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
        return parent::store([
            'workflow_id' => $workflowId,
            ...$stage,
        ]);
    }

    public function attachWidgets($progressTracker, array $widgets, bool $isUpdate = false): void
    {
        if (!empty($widgets)) {
            $existingWidgetIds = $progressTracker->widgets->pluck('id')->toArray();

            foreach ($widgets as $obj) {
                $widget = $this->widgetRepository->find($obj['value']);
                if ($widget && !in_array($widget->id, $existingWidgetIds)) {
                    $progressTracker->widgets()->attach($widget->id);
                }
            }

            if ($isUpdate) {
                $this->detachWidgets($progressTracker, $widgets);
            }
        }
    }

    private function detachWidgets($progressTracker, array $widgets): void
    {
        if (!empty($widgets)) {
            $existingWidgetIds = $progressTracker->widgets->pluck('id')->toArray();
            $frontEndIds = array_column($widgets, 'value');
            $toDelete = array_diff($existingWidgetIds, $frontEndIds);

            if (!empty($toDelete)) {
                $progressTracker->widgets()->detach($toDelete);
            }
        }
    }

    /**
     * Attach recipients to the progress tracker.
     *
     * @param mixed $progressLine
     * @param array $recipients
     * @param bool $isUpdate
     * @return void
     */
    private function attachRecipients($progressLine, array $recipients, bool $isUpdate = false): void
    {
        if (!empty($recipients)) {
            $existingRecipientIds = $progressLine->recipients->pluck('id')->toArray();

            foreach ($recipients as $obj) {
                $recipient = $this->mailingListRepository->find($obj['value']);
                if ($recipient && !in_array($recipient->id, $existingRecipientIds)) {
                    $progressLine->recipients()->save($recipient);
                }
            }

            if ($isUpdate) {
                $this->detachRecipients($progressLine, $recipients);
            }
        }
    }

    private function detachRecipients($progressLine, array $recipients): void
    {
        if (!empty($recipients)) {
            $existingRecipientIds = $progressLine->recipients->pluck('id')->toArray();
            $frontEndIds = array_column($recipients, 'value');
            $toDelete = array_diff($existingRecipientIds, $frontEndIds);

            if (!empty($toDelete)) {
                $progressLine->recipients()->detach($toDelete);
            }
        }
    }

    /**
     * Attach actions to the progress tracker.
     *
     * @param mixed $progressLine
     * @param array $actions
     * @return void
     */
    private function attachActions($progressLine, array $actions, bool $isUpdate = false): void
    {
        if (!empty($actions)) {
            $existingActionIds = $progressLine->actions->pluck('id')->toArray();

            foreach ($actions as $obj) {
                $action = $this->documentActionRepository->find($obj['value']);
                if ($action && !in_array($action->id, $existingActionIds)) {
                    $progressLine->actions()->save($action);
                }
            }

            if ($isUpdate) {
                $this->detachActions($progressLine, $actions);
            }
        }
    }

    private function detachActions($progressLine, array $actions): void
    {
        if (!empty($actions)) {
            $existingActionIds = $progressLine->actions->pluck('id')->toArray();
            $toDelete = array_diff($existingActionIds, array_column($actions, 'value'));
            if (!empty($toDelete)) {
                $progressLine->actions()->detach($toDelete);
            }
        }
    }
}
