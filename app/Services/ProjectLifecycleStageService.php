<?php

namespace App\Services;

use App\Repositories\ProjectLifecycleStageRepository;

class ProjectLifecycleStageService extends BaseService
{
    public function __construct(ProjectLifecycleStageRepository $projectLifecycleStageRepository)
    {
        parent::__construct($projectLifecycleStageRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
