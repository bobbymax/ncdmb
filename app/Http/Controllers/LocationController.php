<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(LocationService $locationService) {
        parent::__construct($locationService, 'Location', LocationResource::class);
    }
}
