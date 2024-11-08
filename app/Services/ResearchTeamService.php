<?php

namespace App\Services;

use App\Repositories\ResearchTeamRepository;

class ResearchTeamService extends BaseService
{
    public function __construct(ResearchTeamRepository $researchTeamRepository)
    {
        $this->repository = $researchTeamRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'name' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'engagement_date' => 'required|date',
            'rank' => 'nullable|string|max:255',
            'engagement_nature' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
        ];
    }
}
