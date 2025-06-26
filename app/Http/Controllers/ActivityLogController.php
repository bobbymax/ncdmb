<?php

namespace App\Http\Controllers;


use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;

class ActivityLogController extends BaseController
{
    public function __construct(ActivityLogService $activityLogService) {
        parent::__construct($activityLogService, 'ActivityLog', ActivityLogResource::class);
    }
}
