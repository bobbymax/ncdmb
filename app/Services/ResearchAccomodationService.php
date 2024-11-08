<?php

namespace App\Services;

use App\Repositories\ResearchAccomodationRepository;

class ResearchAccomodationService extends BaseService
{
    public function __construct(ResearchAccomodationRepository $researchAccomodationRepository)
    {
        $this->repository = $researchAccomodationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'rank' => 'required|string|max:255',
            'average_area' => 'nullable|string|max:255',
            'no_of_occupants' => 'required|integer|min:0',
            'occupancy_type' => 'required|string|max:255|in:single,shared',
            'remark' => 'nullable|string|min:3',
        ];
    }
}
