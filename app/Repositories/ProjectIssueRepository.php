<?php

namespace App\Repositories;

use App\Models\ProjectIssue;

class ProjectIssueRepository extends BaseRepository
{
    public function __construct(ProjectIssue $projectIssue) {
        parent::__construct($projectIssue);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
