<?php

namespace App\Repositories;

use App\Models\Expense;
use Carbon\Carbon;

class ExpenseRepository extends BaseRepository
{
    public function __construct(Expense $expense) {
        parent::__construct($expense);
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
