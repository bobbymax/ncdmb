<?php

namespace App\Repositories;

use App\Models\ResearchAccomodation;

class ResearchAccomodationRepository extends BaseRepository
{
    public function __construct(ResearchAccomodation $researchAccomodation) {
        parent::__construct($researchAccomodation);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
