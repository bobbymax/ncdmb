<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(TransactionService $transactionService) {
        parent::__construct($transactionService, 'Transaction', TransactionResource::class);
    }
}
