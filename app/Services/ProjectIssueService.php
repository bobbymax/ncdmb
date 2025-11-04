<?php

namespace App\Services;

use App\Repositories\ProjectIssueRepository;

class ProjectIssueService extends BaseService
{
    public function __construct(ProjectIssueRepository $projectIssueRepository)
    {
        parent::__construct($projectIssueRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
