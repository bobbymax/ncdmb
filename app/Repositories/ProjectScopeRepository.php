<?php

namespace App\Repositories;

use App\Models\ProjectScope;
use Carbon\Carbon;

class ProjectScopeRepository extends BaseRepository
{
    public function __construct(ProjectScope $projectScope) {
        parent::__construct($projectScope);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'expected_start_date' => Carbon::parse($data['expected_start_date']),
            'expected_completion_date' => Carbon::parse($data['expected_completion_date']),
        ];
    }
}
