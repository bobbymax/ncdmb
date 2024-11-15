<?php

namespace App\Http\Controllers;

use App\Http\Resources\BudgetPlanResource;
use App\Services\BudgetPlanService;

class BudgetPlanController extends Controller
{
    public function __construct(BudgetPlanService $budgetPlanService) {
        parent::__construct($budgetPlanService, 'BudgetPlan', BudgetPlanResource::class);
    }
}
