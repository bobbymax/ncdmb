<?php

namespace App\Services;

use App\Repositories\TransactionRepository;

class TransactionService extends BaseService
{
    public function __construct(TransactionRepository $transactionRepository)
    {
        parent::__construct($transactionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
