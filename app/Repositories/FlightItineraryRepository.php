<?php

namespace App\Repositories;

use App\Models\FlightItinerary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FlightItineraryRepository extends BaseRepository
{
    public function __construct(FlightItinerary $flightItinerary) {
        parent::__construct($flightItinerary);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'takeoff_departure_date' => Carbon::parse($data['takeoff_departure_date']),
            'takeoff_arrival_date' => Carbon::parse($data['takeoff_arrival_date']),
            'return_departure_date' => Carbon::parse($data['return_departure_date']),
            'return_arrival_date' => Carbon::parse($data['return_arrival_date']),
        ];
    }
}
