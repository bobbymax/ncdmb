<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProgressTrackerResource;
use App\Services\ProgressTrackerService;

class ProgressTrackerController extends BaseController
{
    public function __construct(ProgressTrackerService $progressTrackerService) {
        parent::__construct($progressTrackerService, 'ProgressTracker', ProgressTrackerResource::class);
    }
}
