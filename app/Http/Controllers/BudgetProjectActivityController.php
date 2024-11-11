<?php

namespace App\Http\Controllers;

use App\Services\BudgetProjectActivityService;

class BudgetProjectActivityController extends Controller
{
    public function __construct(BudgetProjectActivityService $budgetProjectActivityService) {
        parent::__construct($budgetProjectActivityService, 'BudgetProjectActivity');
    }
}
