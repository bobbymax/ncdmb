<?php

namespace App\Services;

use App\Repositories\ResearchTeamDevelopmentRepository;

class ResearchTeamDevelopmentService extends BaseService
{
    public function __construct(ResearchTeamDevelopmentRepository $researchTeamDevelopmentRepository)
    {
        $this->repository = $researchTeamDevelopmentRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'c_d_activity_id' => 'required|integer|exists:c_d_activities,id',
            'title' => 'required|string|max:255',
            'no_of_persons' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
        ];
    }
}
