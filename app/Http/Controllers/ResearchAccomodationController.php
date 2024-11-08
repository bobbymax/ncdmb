<?php

namespace App\Http\Controllers;

use App\Services\ResearchAccomodationService;

class ResearchAccomodationController extends Controller
{
    public function __construct(ResearchAccomodationService $researchAccomodationService) {
        $this->service = $researchAccomodationService;
        $this->name = 'ResearchAccomodation';
    }
}
