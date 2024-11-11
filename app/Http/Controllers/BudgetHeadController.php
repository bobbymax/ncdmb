<?php

namespace App\Http\Controllers;

use App\Services\BudgetHeadService;

class BudgetHeadController extends Controller
{
    public function __construct(BudgetHeadService $budgetHeadService) {
        $this->service = $budgetHeadService;
        $this->name = 'BudgetHead';
    }
}
