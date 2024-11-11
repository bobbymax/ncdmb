<?php

namespace App\Repositories;

use App\Models\Fund;

class FundRepository extends BaseRepository
{
    public function __construct(Fund $fund) {
        parent::__construct($fund);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'total_booked_balance' => $data['total_approved_amount'],
            'total_actual_balance' => $data['total_approved_amount'],
        ];
    }
}
