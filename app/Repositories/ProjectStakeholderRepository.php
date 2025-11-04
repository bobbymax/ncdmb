<?php

namespace App\Repositories;

use App\Models\ProjectStakeholder;

class ProjectStakeholderRepository extends BaseRepository
{
    public function __construct(ProjectStakeholder $projectStakeholder) {
        parent::__construct($projectStakeholder);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
