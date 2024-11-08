<?php

namespace App\Services;

use App\Repositories\VesselUtilizationRepository;

class VesselUtilizationService extends BaseService
{
    public function __construct(VesselUtilizationRepository $vesselUtilizationRepository)
    {
        $this->repository = $vesselUtilizationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'vessel_id' => 'required|integer|exists:vessels,id',
            'contractor_id' => 'required|integer|exists:companies,id',
            'year' => 'required|integer|digits:4',
            'period' => 'required|string|max:255',
            'no_of_nigerians' => 'required|integer',
            'no_of_expatriates' => 'required|integer',
        ];
    }
}
