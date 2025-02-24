<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
use Illuminate\Support\Facades\DB;

class ProgressTrackerService extends BaseService
{
    protected DocumentActionRepository $documentActionRepository;
    protected MailingListRepository $mailingListRepository;

    public function __construct(
        ProgressTrackerRepository $progressTrackerRepository,
        DocumentActionRepository $documentActionRepository,
        MailingListRepository $mailingListRepository
    ) {
        parent::__construct($progressTrackerRepository);
        $this->documentActionRepository = $documentActionRepository;
        $this->mailingListRepository = $mailingListRepository;
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
            'stages.*.group_id' => ['required', 'integer', 'min:1', 'exists:groups,id'],
            'stages.*.department_id' => $this->departmentValidationRule(),
            'stages.*.carder_id' => ['required', 'integer', 'min:1', 'exists:carders,id'],
            'stages.*.document_type_id' => ['required', 'integer', 'min:1', 'exists:document_types,id'],
            'stages.*.order' => ['required', 'integer', 'between:1,100'],
            'stages.*.actions' => ['required', 'array'],
            'stages.*.recipients' => ['required', 'array'],
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
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['stages'])) {
                return null;
            }

            $trackers = collect();

            foreach ($data['stages'] as $stage) {
                $progressLine = $this->createProgressTracker($data['workflow_id'], $stage);

                $this->attachRecipients($progressLine, $stage['recipients'] ?? []);
                $this->attachActions($progressLine, $stage['actions'] ?? []);

                $trackers->push($progressLine);
            }

            return $trackers->first();
        });
    }

    /**
     * Create a new progress tracker record.
     *
     * @param int $workflowId
     * @param array $stage
     * @return mixed
     */
    private function createProgressTracker(int $workflowId, array $stage): mixed
    {
        return parent::store([
            'workflow_id' => $workflowId,
            ...$stage,
        ]);
    }

    /**
     * Attach recipients to the progress tracker.
     *
     * @param mixed $progressLine
     * @param array $recipients
     * @return void
     */
    private function attachRecipients($progressLine, array $recipients): void
    {
        if (!empty($recipients)) {
            $existingRecipientIds = $progressLine->recipients->pluck('id')->toArray();

            foreach ($recipients as $obj) {
                $recipient = $this->mailingListRepository->find($obj['value']);
                if ($recipient && !in_array($recipient->id, $existingRecipientIds)) {
                    $progressLine->recipients()->save($recipient);
                }
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
    private function attachActions($progressLine, array $actions): void
    {
        if (!empty($actions)) {
            $existingActionIds = $progressLine->actions->pluck('id')->toArray();

            foreach ($actions as $obj) {
                $action = $this->documentActionRepository->find($obj['value']);
                if ($action && !in_array($action->id, $existingActionIds)) {
                    $progressLine->actions()->save($action);
                }
            }
        }
    }
}
