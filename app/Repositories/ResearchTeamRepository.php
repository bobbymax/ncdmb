<?php

namespace App\Repositories;

use App\Models\ResearchTeam;
use Carbon\Carbon;

class ResearchTeamRepository extends BaseRepository
{
    public function __construct(ResearchTeam $researchTeam) {
        parent::__construct($researchTeam);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'engagement_date' => Carbon::parse($data['engagement_date']),
        ];
    }
}
