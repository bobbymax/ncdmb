<?php

namespace App\Http\Controllers;

use App\Services\HotelService;

class HotelController extends Controller
{
    public function __construct(HotelService $hotelService) {
        parent::__construct($hotelService, 'Hotel');
    }
}