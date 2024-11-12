<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MeetingRepository extends BaseRepository
{
    public function __construct(Meeting $meeting) {
        parent::__construct($meeting);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return [
            ...$data,
            'start_date' => Carbon::parse($data['start_date']),
            'end_date' => Carbon::parse($data['end_date']),
            'date_approved_or_declined' => Carbon::parse($data['date_approved_or_declined']),
            'start_time' => Carbon::parse($data['start_time'])->toTimeString(),
            'end_time' => Carbon::parse($data['end_time'])->toTimeString(),
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $data['code'] ?? $this->generate('code', 'MER'),
        ];
    }
}
