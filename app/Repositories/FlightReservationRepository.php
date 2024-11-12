<?php

namespace App\Repositories;

use App\Models\FlightReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FlightReservationRepository extends BaseRepository
{
    public function __construct(FlightReservation $flightReservation) {
        parent::__construct($flightReservation);
    }

    public function parse(array $data): array
    {
        unset($data['approval_memo']);
        unset($data['visa']);
        unset($data['data_page']);

        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->departmemnt_id,
            'departure_date' => Carbon::parse($data['departure_date']),
            'return_date' => Carbon::parse($data['return_date']),
            'code' => $data['code'] ?? $this->generate('code', 'FRV'),
        ];
    }
}
