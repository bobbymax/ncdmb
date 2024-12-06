<?php

namespace App\Http\Controllers;

use App\Http\Resources\BudgetProjectActivityResource;
use App\Services\BudgetProjectActivityService;

class BudgetProjectActivityController extends BaseController
{
    public function __construct(BudgetProjectActivityService $budgetProjectActivityService) {
        parent::__construct($budgetProjectActivityService, 'BudgetProjectActivity', BudgetProjectActivityResource::class);
    }
}
