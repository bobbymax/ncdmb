<?php

namespace App\Http\Controllers;

use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(BuildingService $buildingService) {
        parent::__construct($buildingService, 'Building');
    }
}
