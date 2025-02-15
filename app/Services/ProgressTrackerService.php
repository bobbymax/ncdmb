<?php

namespace App\Services;

use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
use Illuminate\Support\Facades\DB;

class ProgressTrackerService extends BaseService
{
    protected MailingListRepository $mailingListRepository;
    protected DocumentActionRepository $documentActionRepository;
    public function __construct(
        ProgressTrackerRepository $progressTrackerRepository,
        MailingListRepository $mailingListRepository,
        DocumentActionRepository $documentActionRepository
    ) {
        parent::__construct($progressTrackerRepository);
        $this->mailingListRepository = $mailingListRepository;
        $this->documentActionRepository = $documentActionRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'stages' => 'required|array',
            'stages.*.workflow_stage_id' => 'required|integer|exists:workflow_stages,id',
            'stages.*.fallback_to_stage_id' => [
                'sometimes',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value > 0 && !DB::table('workflow_stages')->where('id', $value)->exists()) {
                        $fail('The selected fallback stage does not exist in our database.');
                    }
                }
            ],
            'stages.*.return_to_stage_id' => [
                'sometimes',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value > 0 && !DB::table('workflow_stages')->where('id', $value)->exists()) {
                        $fail('The selected response stage does not exist in our database.');
                    }
                }
            ],
            'stages.*.document_type_id' => 'required|integer|min:1|exists:document_types,id',
            'stages.*.order' => 'required|integer|between:1,100',
            'stages.*.actions' => 'required|array',
            'stages.*.recipients' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (!isset($data['stages'])) {
                return null;
            }

            $trackers = collect();

            foreach ($data['stages'] as $stage) {
                $fill = [
                    'workflow_id' => $data['workflow_id'],
                    ...$stage
                ];

                $progressLine = parent::store($fill);
                $trackers->push($progressLine);
            }

            $this->batchAddTrackersActions($trackers, $data['stages']);
            $this->batchAddTrackersRecipients($trackers, $data['stages']);

            return $trackers->first();
        });
    }

    protected function batchAddTrackersActions($trackers, array $stages): void
    {
        $actionIds = collect($stages)->flatMap(fn($stage) => collect($stage['actions'])->pluck('value'))->unique();
        $actions = $this->documentActionRepository->findMany($actionIds->toArray())->keyBy('id');

        foreach ($trackers as $index => $progressTracker) {
            foreach ($stages[$index]['actions'] as $selection) {
                if ($actions->has($selection['value'])) {
                    $progressTracker->actions()->attach($actions[$selection['value']]);
                }
            }
        }
    }

    protected function batchAddTrackersRecipients($trackers, array $stages): void
    {
        $recipientIds = collect($stages)->flatMap(fn($stage) => collect($stage['recipients'])->pluck('value'))->unique();
        $recipients = $this->mailingListRepository->findMany($recipientIds->toArray())->keyBy('id');

        foreach ($trackers as $index => $progressTracker) {
            foreach ($stages[$index]['recipients'] as $selection) {
                if ($recipients->has($selection['value'])) {
                    $progressTracker->recipients()->attach($recipients[$selection['value']]);
                }
            }
        }
    }
}
