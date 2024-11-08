<?php

namespace App\Repositories;

use App\Models\ResearchTeamDevelopment;
use Carbon\Carbon;

class ResearchTeamDevelopmentRepository extends BaseRepository
{
    public function __construct(ResearchTeamDevelopment $researchTeamDevelopment) {
        parent::__construct($researchTeamDevelopment);
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
