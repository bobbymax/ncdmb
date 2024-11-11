<?php

namespace App\Services;

use App\Http\Resources\BudgetPlanResource;
use App\Repositories\BudgetPlanRepository;

class BudgetPlanService extends BaseService
{
    public function __construct(BudgetPlanRepository $budgetPlanRepository, BudgetPlanResource $budgetPlanResource)
    {
        parent::__construct($budgetPlanRepository, $budgetPlanResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'sub_budget_head_id' => 'required|integer|exists:sub_budget_heads,id',
            'total_proposed_amount' => 'required|numeric|min:1',
            'total_revised_amount' => 'sometimes|numeric|min:0',
            'total_approved_amount' => 'sometimes|numeric|min:0',
            'budget_year' => 'required|integer|digits:4',
            'is_approved' => 'sometimes|boolean',
        ];
    }
}
