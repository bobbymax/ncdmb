<?php

namespace App\Repositories;

use App\Models\ResearchDissemination;
use Carbon\Carbon;

class ResearchDisseminationRepository extends BaseRepository
{
    public function __construct(ResearchDissemination $researchDissemination) {
        parent::__construct($researchDissemination);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'publication_date' => Carbon::parse($data['publication_date']) ?? null,
        ];
    }
}
