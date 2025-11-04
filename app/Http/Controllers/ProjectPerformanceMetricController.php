<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectPerformanceMetricResource;
use App\Services\ProjectPerformanceMetricService;

class ProjectPerformanceMetricController extends BaseController
{
    public function __construct(ProjectPerformanceMetricService $projectPerformanceMetricService) {
        parent::__construct($projectPerformanceMetricService, 'ProjectPerformanceMetric', ProjectPerformanceMetricResource::class);
    }
}
