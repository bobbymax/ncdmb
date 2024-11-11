<?php

namespace App\Http\Controllers;

use App\Services\BudgetPlanService;

class BudgetPlanController extends Controller
{
    public function __construct(BudgetPlanService $budgetPlanService) {
        $this->service = $budgetPlanService;
        $this->name = 'BudgetPlan';
    }
}
