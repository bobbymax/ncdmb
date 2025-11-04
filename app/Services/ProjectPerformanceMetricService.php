<?php

namespace App\Services;

use App\Repositories\ProjectPerformanceMetricRepository;

class ProjectPerformanceMetricService extends BaseService
{
    public function __construct(ProjectPerformanceMetricRepository $projectPerformanceMetricRepository)
    {
        parent::__construct($projectPerformanceMetricRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
