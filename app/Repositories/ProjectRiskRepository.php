<?php

namespace App\Repositories;

use App\Models\ProjectRisk;

class ProjectRiskRepository extends BaseRepository
{
    public function __construct(ProjectRisk $projectRisk) {
        parent::__construct($projectRisk);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
