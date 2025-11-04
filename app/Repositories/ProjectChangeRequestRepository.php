<?php

namespace App\Repositories;

use App\Models\ProjectChangeRequest;

class ProjectChangeRequestRepository extends BaseRepository
{
    public function __construct(ProjectChangeRequest $projectChangeRequest) {
        parent::__construct($projectChangeRequest);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
