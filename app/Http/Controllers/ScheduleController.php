<?php

namespace App\Http\Controllers;

use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(ScheduleService $scheduleService) {
        $this->service = $scheduleService;
        $this->name = 'Schedule';
    }
}
