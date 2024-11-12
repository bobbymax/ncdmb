<?php

namespace App\Http\Controllers;

use App\Services\FlightReservationService;

class FlightReservationController extends Controller
{
    public function __construct(FlightReservationService $flightReservationService) {
        parent::__construct($flightReservationService, 'FlightReservation');
    }
}
