<?php

namespace App\Repositories;

use App\Models\ProjectProgram;

class ProjectProgramRepository extends BaseRepository
{
    public function __construct(ProjectProgram $projectProgram) {
        parent::__construct($projectProgram);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
