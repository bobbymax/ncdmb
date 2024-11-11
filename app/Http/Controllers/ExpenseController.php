<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;

class ExpenseController extends Controller
{
    public function __construct(ExpenseService $expenseService) {
        $this->service = $expenseService;
        $this->name = 'Expense';
    }
}
