<?php

namespace App\Http\Controllers;

use App\Http\Resources\BudgetCodeResource;
use App\Services\BudgetCodeService;

class BudgetCodeController extends BaseController
{
    public function __construct(BudgetCodeService $budgetCodeService) {
        parent::__construct($budgetCodeService, 'BudgetCode', BudgetCodeResource::class);
    }
}
