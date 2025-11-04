<?php

namespace App\Repositories;

use App\Models\ProjectPerformanceMetric;

class ProjectPerformanceMetricRepository extends BaseRepository
{
    public function __construct(ProjectPerformanceMetric $projectPerformanceMetric) {
        parent::__construct($projectPerformanceMetric);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
