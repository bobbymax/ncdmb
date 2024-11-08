<?php

namespace App\Repositories;

use App\Models\ResearchCentre;

class ResearchCentreRepository extends BaseRepository
{
    public function __construct(ResearchCentre $researchCentre) {
        parent::__construct($researchCentre);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
