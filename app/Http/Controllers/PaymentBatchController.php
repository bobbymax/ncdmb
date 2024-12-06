<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentBatchResource;
use App\Services\PaymentBatchService;

class PaymentBatchController extends BaseController
{
    public function __construct(PaymentBatchService $paymentBatchService) {
        parent::__construct($paymentBatchService, 'Batch', PaymentBatchResource::class);
    }
}
