<?php

namespace App\Http\Controllers;

use App\Services\PaymentBatchService;

class PaymentBatchController extends Controller
{
    public function __construct(PaymentBatchService $paymentBatchService) {
        parent::__construct($paymentBatchService, 'Batch');
    }
}
