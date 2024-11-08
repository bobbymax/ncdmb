<?php

namespace App\Repositories;

use App\Models\ResearchOutcome;
use Carbon\Carbon;

class ResearchOutcomeRepository extends BaseRepository
{
    public function __construct(ResearchOutcome $researchOutcome) {
        parent::__construct($researchOutcome);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'date' => Carbon::parse($data['date']),
        ];
    }
}
