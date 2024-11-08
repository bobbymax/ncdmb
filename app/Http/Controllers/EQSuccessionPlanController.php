<?php

namespace App\Http\Controllers;

use App\Services\EQSuccessionPlanService;

class EQSuccessionPlanController extends Controller
{
    public function __construct(EQSuccessionPlanService $eQSuccessionPlanService) {
        $this->service = $eQSuccessionPlanService;
        $this->name = 'EQSuccessionPlan';
    }
}
