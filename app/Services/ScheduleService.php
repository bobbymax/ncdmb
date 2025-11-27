<?php

namespace App\Services;

use App\Repositories\ScheduleRepository;

class ScheduleService extends BaseService
{
    public function __construct(ScheduleRepository $scheduleRepository)
    {
        parent::__construct($scheduleRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'parent_id' => 'nullable|integer|exists:schedules,id',
            'description' => 'required|string|min:3',
            'nc_percentage' => 'nullable|integer|min:0|max:100',
            'measuring_unit' => 'nullable|string|max:255',
        ];
    }
}
