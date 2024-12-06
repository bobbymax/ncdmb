<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;

class ExpenseController extends BaseController
{
    public function __construct(ExpenseService $expenseService) {
        parent::__construct($expenseService, 'Expense', ExpenseResource::class);
    }
}
