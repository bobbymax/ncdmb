<?php

namespace App\Services;

use App\Repositories\ProjectProgramRepository;

class ProjectProgramService extends BaseService
{
    public function __construct(ProjectProgramRepository $projectProgramRepository)
    {
        parent::__construct($projectProgramRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
