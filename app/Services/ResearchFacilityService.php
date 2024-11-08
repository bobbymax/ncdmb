<?php

namespace App\Services;

use App\Repositories\ResearchFacilityRepository;

class ResearchFacilityService extends BaseService
{
    public function __construct(ResearchFacilityRepository $researchFacilityRepository)
    {
        $this->repository = $researchFacilityRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'focus_area' => 'nullable|string|min:3',
            'average_area' => 'nullable|string|max:255',
            'equipment_lacking' => 'nullable|string|min:3',
            'facility_type' => 'required|string|in:laboratory,workshop',
        ];
    }
}
