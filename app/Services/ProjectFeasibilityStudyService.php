<?php

namespace App\Services;

use App\Repositories\ProjectFeasibilityStudyRepository;

class ProjectFeasibilityStudyService extends BaseService
{
    public function __construct(ProjectFeasibilityStudyRepository $projectFeasibilityStudyRepository)
    {
        parent::__construct($projectFeasibilityStudyRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
