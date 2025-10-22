<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use App\Services\ProcessCardExecutionService;
use Illuminate\Support\Facades\Log;

class ExecuteProcessCardOnPaymentApproved
{
    protected ProcessCardExecutionService $service;
    
    public function __construct(ProcessCardExecutionService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the event
     */
    public function handle(PaymentApproved $event): void
    {
        $payment = $event->payment;
        
        if (!$payment->processCard) {
            return;
        }
        
        $rules = $payment->processCard->rules;
        
        // Check if auto-execution on approval is enabled
        if ($rules['auto_execute_on_approval'] ?? false) {
            try {
                $steps = $this->service->executeWithRetry($payment, $payment->processCard);
                
                Log::info("ProcessCard auto-executed on payment approval", [
                    'payment_id' => $payment->id,
                    'process_card_id' => $payment->processCard->id,
                    'steps_completed' => count($steps)
                ]);
            } catch (\Exception $e) {
                Log::error("ProcessCard execution failed on approval", [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

