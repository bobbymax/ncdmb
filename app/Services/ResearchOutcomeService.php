<?php

namespace App\Services;

use App\Repositories\ResearchOutcomeRepository;

class ResearchOutcomeService extends BaseService
{
    public function __construct(ResearchOutcomeRepository $researchOutcomeRepository)
    {
        $this->repository = $researchOutcomeRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'milestone' => 'required|string|max:255',
            'date' => 'required|date',
            'outcome' => 'nullable|string|min:5',
            'remark' => 'nullable|string|min:5',
        ];
    }
}
