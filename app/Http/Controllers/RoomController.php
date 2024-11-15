<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Services\RoomService;

class RoomController extends Controller
{
    public function __construct(RoomService $roomService) {
        parent::__construct($roomService, 'Room', RoomResource::class);
    }
}
