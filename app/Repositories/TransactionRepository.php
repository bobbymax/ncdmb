<?php

namespace App\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $transaction) {
        parent::__construct($transaction);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'transaction_date' => Carbon::parse($data['transaction_date']),
            'date_posted' => isset($data['date_posted']) ? Carbon::parse($data['date_posted']) : null,
        ];
    }
}
