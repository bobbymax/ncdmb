<?php

namespace App\Repositories;

use App\Models\RNDProject;
use Carbon\Carbon;

class RNDProjectRepository extends BaseRepository
{
    public function __construct(RNDProject $rNDProject) {
        parent::__construct($rNDProject);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'start_date' => Carbon::parse($data['start_date']) ?? null,
            'completion_date' => Carbon::parse($data['completion_date']) ?? null,
        ];
    }
}
