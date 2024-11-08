<?php

namespace App\Repositories;

use App\Models\EQEmployee;
use Carbon\Carbon;

class EQEmployeeRepository extends BaseRepository
{
    public function __construct(EQEmployee $eQEmployee) {
        parent::__construct($eQEmployee);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'start_date' => Carbon::parse($data['start_date']),
            'end_date' => Carbon::parse($data['end_date']),
        ];
    }
}
