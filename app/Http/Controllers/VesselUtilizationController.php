<?php

namespace App\Http\Controllers;

use App\Services\VesselUtilizationService;

class VesselUtilizationController extends Controller
{
    public function __construct(VesselUtilizationService $vesselUtilizationService) {
        $this->service = $vesselUtilizationService;
        $this->name = 'VesselUtilization';
    }
}
