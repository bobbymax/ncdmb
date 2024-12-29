<?php

namespace App\Repositories;

use App\Models\Trip;
use Carbon\Carbon;

class TripRepository extends BaseRepository
{
    public function __construct(Trip $trip) {
        parent::__construct($trip);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'departure_date' => Carbon::parse($data['departure_date']),
            'return_date' => Carbon::parse($data['return_date']),
        ];
    }
}
