<?php

namespace App\Http\Controllers;


use App\Http\Resources\TripResource;
use App\Services\TripService;

class TripController extends BaseController
{
    public function __construct(TripService $tripService) {
        parent::__construct($tripService, 'Trip', TripResource::class);
    }
}
