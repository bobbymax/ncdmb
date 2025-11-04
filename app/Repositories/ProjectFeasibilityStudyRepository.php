<?php

namespace App\Repositories;

use App\Models\ProjectFeasibilityStudy;

class ProjectFeasibilityStudyRepository extends BaseRepository
{
    public function __construct(ProjectFeasibilityStudy $projectFeasibilityStudy) {
        parent::__construct($projectFeasibilityStudy);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
