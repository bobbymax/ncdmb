<?php

namespace App\Services;

use App\Repositories\ScheduleRepository;

class ScheduleService extends BaseService
{
    public function __construct(ScheduleRepository $scheduleRepository)
    {
        $this->repository = $scheduleRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:schedules',
            'measurement_unit' => 'required|string|max:255',
        ];
    }
}
