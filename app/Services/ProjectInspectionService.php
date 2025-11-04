<?php

namespace App\Services;

use App\Repositories\ProjectInspectionRepository;

class ProjectInspectionService extends BaseService
{
    public function __construct(ProjectInspectionRepository $projectInspectionRepository)
    {
        parent::__construct($projectInspectionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
