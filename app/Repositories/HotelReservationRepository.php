<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\HotelReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HotelReservationRepository extends BaseRepository
{
    public function __construct(HotelReservation $hotelReservation) {
        parent::__construct($hotelReservation);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $this->generate('code', 'HRS'),
            'check_in_date' => Carbon::parse($data['check_in_date']),
            'check_out_date' => Carbon::parse($data['check_out_date']),
            'check_in_time' => Carbon::parse($data['check_in_time'])->toTimeString(),
        ];
    }
}
