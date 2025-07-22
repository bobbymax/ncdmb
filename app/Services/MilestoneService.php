<?php

namespace App\Services;

use App\Repositories\MilestoneRepository;

class MilestoneService extends BaseService
{
    public function __construct(MilestoneRepository $milestoneRepository)
    {
        parent::__construct($milestoneRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'milestoneable_id' => 'required|integer',
            'milestoneable_type' => 'required|string',
            'description' => 'required|string',
            'percentage_completion' => 'required|numeric',
            'duration' => 'required|integer',
            'frequency' => 'required|string|in:day,week,month,year',
            'status' => 'required|in:active,inactive',
            'is_closed' => 'required|in:0,1',
        ];
    }
}
