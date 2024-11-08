<?php

namespace App\Services;

use App\Repositories\ResearchBudgetRepository;

class ResearchBudgetService extends BaseService
{
    public function __construct(ResearchBudgetRepository $researchBudgetRepository)
    {
        $this->repository = $researchBudgetRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'item' => 'required|string|max:255',
            'year' => 'required|integer|digits:4',
            'planned_amount' => 'sometimes|numeric|min:0',
            'actual_amount' => 'sometimes|numeric|min:0',
            'item_origin' => 'required|string|max:255|in:nigerian,foreign',
        ];
    }
}
