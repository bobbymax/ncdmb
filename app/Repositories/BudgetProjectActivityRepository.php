<?php

namespace App\Repositories;

use App\Models\BudgetProjectActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BudgetProjectActivityRepository extends BaseRepository
{
    public function __construct(BudgetProjectActivity $budgetProjectActivity) {
        parent::__construct($budgetProjectActivity);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'proposed_start_date' => Carbon::parse($data['proposed_start_date']),
            'proposed_completion_date' => Carbon::parse($data['proposed_completion_date']),
        ];
    }
}
