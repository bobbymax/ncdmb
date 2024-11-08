<?php

namespace App\Http\Controllers;

use App\Services\ResearchBudgetService;

class ResearchBudgetController extends Controller
{
    public function __construct(ResearchBudgetService $researchBudgetService) {
        $this->service = $researchBudgetService;
        $this->name = 'ResearchBudget';
    }
}
