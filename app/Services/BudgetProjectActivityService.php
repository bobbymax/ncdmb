<?php

namespace App\Services;

use App\Repositories\BudgetProjectActivityRepository;

class BudgetProjectActivityService extends BaseService
{
    public function __construct(
        BudgetProjectActivityRepository $budgetProjectActivityRepository
    ) {
        parent::__construct($budgetProjectActivityRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'budget_plan_id' => 'required|integer|exists:budget_plans,id',
            'activity_title' => 'required|string|max:255',
            'description' => 'required|string|min:3',
            'proposed_amount' => 'required|numeric|min:1',
            'proposed_start_date' => 'required|date',
            'proposed_completion_date' => 'required|date|after_or_equal:proposed_start_date',
        ];
    }
}
