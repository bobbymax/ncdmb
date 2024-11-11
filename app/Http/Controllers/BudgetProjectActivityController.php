<?php

namespace App\Http\Controllers;

use App\Services\BudgetProjectActivityService;

class BudgetProjectActivityController extends Controller
{
    public function __construct(BudgetProjectActivityService $budgetProjectActivityService) {
        $this->service = $budgetProjectActivityService;
        $this->name = 'BudgetProjectActivity';
    }
}
