<?php

namespace App\Repositories;

use App\Models\ResearchFacility;

class ResearchFacilityRepository extends BaseRepository
{
    public function __construct(ResearchFacility $researchFacility) {
        parent::__construct($researchFacility);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
