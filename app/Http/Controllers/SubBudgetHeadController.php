<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubBudgetHeadResource;
use App\Services\SubBudgetHeadService;

class SubBudgetHeadController extends Controller
{
    public function __construct(SubBudgetHeadService $subBudgetHeadService) {
        parent::__construct($subBudgetHeadService, 'Sub Budget Head', SubBudgetHeadResource::class);
    }
}
