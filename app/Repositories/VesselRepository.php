<?php

namespace App\Repositories;

use App\Models\Vessel;

class VesselRepository extends BaseRepository
{
    public function __construct(Vessel $vessel) {
        parent::__construct($vessel);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
