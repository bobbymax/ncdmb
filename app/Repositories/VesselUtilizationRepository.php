<?php

namespace App\Repositories;

use App\Models\VesselUtilization;

class VesselUtilizationRepository extends BaseRepository
{
    public function __construct(VesselUtilization $vesselUtilization) {
        parent::__construct($vesselUtilization);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
