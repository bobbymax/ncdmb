<?php

namespace App\Http\Controllers;

use App\Http\Resources\BudgetHeadResource;
use App\Services\BudgetHeadService;

class BudgetHeadController extends BaseController
{
    public function __construct(BudgetHeadService $budgetHeadService) {
        parent::__construct($budgetHeadService, 'BudgetHead', BudgetHeadResource::class);
    }
}
