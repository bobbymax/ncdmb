<?php

namespace App\Http\Controllers;

use App\Services\VesselService;

class VesselController extends Controller
{
    public function __construct(VesselService $vesselService) {
        $this->service = $vesselService;
        $this->name = 'Vessel';
    }
}
