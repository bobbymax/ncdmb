<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;

class ActivityController extends Controller
{
    public function __construct(ActivityService $activityService) {
        $this->service = $activityService;
        $this->name = 'Activity';
    }
}
