<?php

namespace App\Repositories;

use App\Models\Building;

class BuildingRepository extends BaseRepository
{
    public function __construct(Building $building) {
        parent::__construct($building);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
