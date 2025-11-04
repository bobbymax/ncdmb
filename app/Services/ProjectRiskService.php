<?php

namespace App\Services;

use App\Repositories\ProjectRiskRepository;

class ProjectRiskService extends BaseService
{
    public function __construct(ProjectRiskRepository $projectRiskRepository)
    {
        parent::__construct($projectRiskRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
