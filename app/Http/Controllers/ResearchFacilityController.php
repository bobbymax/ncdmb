<?php

namespace App\Http\Controllers;

use App\Services\ResearchFacilityService;

class ResearchFacilityController extends Controller
{
    public function __construct(ResearchFacilityService $researchFacilityService) {
        $this->service = $researchFacilityService;
        $this->name = 'ResearchFacility';
    }
}
