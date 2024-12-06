<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlightReservationResource;
use App\Services\FlightReservationService;

class FlightReservationController extends BaseController
{
    public function __construct(FlightReservationService $flightReservationService) {
        parent::__construct($flightReservationService, 'FlightReservation', FlightReservationResource::class);
    }
}
