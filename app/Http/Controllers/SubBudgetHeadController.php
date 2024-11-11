<?php

namespace App\Http\Controllers;

use App\Services\SubBudgetHeadService;

class SubBudgetHeadController extends Controller
{
    public function __construct(SubBudgetHeadService $subBudgetHeadService) {
        $this->service = $subBudgetHeadService;
        $this->name = 'SubBudgetHead';
    }
}
