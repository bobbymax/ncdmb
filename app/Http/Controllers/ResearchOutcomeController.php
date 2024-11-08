<?php

namespace App\Http\Controllers;

use App\Services\ResearchOutcomeService;

class ResearchOutcomeController extends Controller
{
    public function __construct(ResearchOutcomeService $researchOutcomeService) {
        $this->service = $researchOutcomeService;
        $this->name = 'ResearchOutcome';
    }
}
