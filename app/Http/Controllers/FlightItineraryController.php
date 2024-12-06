<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlightItineraryResource;
use App\Services\FlightItineraryService;

class FlightItineraryController extends BaseController
{
    public function __construct(FlightItineraryService $flightItineraryService) {
        parent::__construct($flightItineraryService, 'FlightItinerary', FlightItineraryResource::class);
    }
}
