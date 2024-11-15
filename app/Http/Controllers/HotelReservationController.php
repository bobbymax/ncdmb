<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelReservationResource;
use App\Services\HotelReservationService;

class HotelReservationController extends Controller
{
    public function __construct(HotelReservationService $hotelReservationService) {
        parent::__construct($hotelReservationService, 'HotelReservation', HotelReservationResource::class);
    }
}
