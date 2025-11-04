<?php

namespace App\Services;

use App\Repositories\ProjectStakeholderRepository;

class ProjectStakeholderService extends BaseService
{
    public function __construct(ProjectStakeholderRepository $projectStakeholderRepository)
    {
        parent::__construct($projectStakeholderRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
