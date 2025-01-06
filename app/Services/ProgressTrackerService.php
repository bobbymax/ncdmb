<?php

namespace App\Services;

use App\Models\ProgressTracker;
use App\Repositories\ProgressTrackerRepository;

class ProgressTrackerService extends BaseService
{
    public function __construct(ProgressTrackerRepository $progressTrackerRepository)
    {
        parent::__construct($progressTrackerRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'workflow_stage_id' => 'required|integer|exists:workflow_stages,id',
            'order' => 'required|integer|between:1,100',
            'date_completed' => 'nullable|date',
            'status' => 'required|string|in:pending,passed,stalled,failed'
        ];
    }
}
