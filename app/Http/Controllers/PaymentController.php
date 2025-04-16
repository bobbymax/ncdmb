<?php

namespace App\Http\Controllers;


use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;

class PaymentController extends BaseController
{
    public function __construct(PaymentService $paymentService) {
        parent::__construct($paymentService, 'Payment', PaymentResource::class);
    }
}
