<?php

namespace App\Http\Controllers;


use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;

class ScheduleController extends BaseController
{
    public function __construct(ScheduleService $scheduleService) {
        parent::__construct($scheduleService, 'Schedule', ScheduleResource::class);
    }
}
