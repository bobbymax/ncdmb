<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(TransactionService $transactionService) {
        $this->service = $transactionService;
        $this->name = 'Transaction';
    }
}
