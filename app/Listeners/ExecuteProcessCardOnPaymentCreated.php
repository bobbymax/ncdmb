<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Services\ProcessCardExecutionService;
use Illuminate\Support\Facades\Log;

class ExecuteProcessCardOnPaymentCreated
{
    protected ProcessCardExecutionService $service;
    
    public function __construct(ProcessCardExecutionService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the event
     */
    public function handle(PaymentCreated $event): void
    {
        $payment = $event->payment;
        
        try {
            // Auto-find and attach ProcessCard
            $processCard = $this->service->autoAttachProcessCard($payment);
            
            if ($processCard) {
                Log::info("ProcessCard auto-attached to Payment", [
                    'payment_id' => $payment->id,
                    'process_card_id' => $processCard->id,
                    'process_card_name' => $processCard->name
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to auto-attach ProcessCard to Payment", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

