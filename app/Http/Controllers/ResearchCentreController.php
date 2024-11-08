<?php

namespace App\Http\Controllers;

use App\Services\ResearchCentreService;

class ResearchCentreController extends Controller
{
    public function __construct(ResearchCentreService $researchCentreService) {
        $this->service = $researchCentreService;
        $this->name = 'ResearchCentre';
    }
}
