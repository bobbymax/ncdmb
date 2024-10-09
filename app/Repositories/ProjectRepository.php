<?php

namespace App\Repositories;

use App\Models\Project;
use Carbon\Carbon;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $project) {
        parent::__construct($project);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            Carbon::parse($data['start_date']),
            Carbon::parse($data['completion_date']),
        ];
    }
}
