<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $transaction) {
        parent::__construct($transaction);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
