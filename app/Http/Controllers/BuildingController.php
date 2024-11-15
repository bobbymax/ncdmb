<?php

namespace App\Http\Controllers;


use App\Http\Resources\BuildingResource;
use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(BuildingService $buildingService) {
        parent::__construct($buildingService, 'Building', BuildingResource::class);
    }
}
