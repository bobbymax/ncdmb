<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated
{
    use Dispatchable, SerializesModels;
    
    public Payment $payment;
    
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}

