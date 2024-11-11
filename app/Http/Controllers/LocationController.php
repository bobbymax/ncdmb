<?php

namespace App\Http\Controllers;

use App\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(LocationService $locationService) {
        $this->service = $locationService;
        $this->name = 'Location';
    }
}
