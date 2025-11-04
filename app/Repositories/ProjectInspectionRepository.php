<?php

namespace App\Repositories;

use App\Models\ProjectInspection;

class ProjectInspectionRepository extends BaseRepository
{
    public function __construct(ProjectInspection $projectInspection) {
        parent::__construct($projectInspection);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
