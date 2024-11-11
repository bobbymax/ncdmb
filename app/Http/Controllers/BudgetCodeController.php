<?php

namespace App\Http\Controllers;

use App\Services\BudgetCodeService;

class BudgetCodeController extends Controller
{
    public function __construct(BudgetCodeService $budgetCodeService) {
        $this->service = $budgetCodeService;
        $this->name = 'BudgetCode';
    }
}
