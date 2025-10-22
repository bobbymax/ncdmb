<?php

namespace App\Observers;

use App\Events\PaymentApproved;
use App\Events\PaymentCreated;
use App\Events\PaymentSettled;
use App\Models\Payment;
use App\Services\ProcessCardExecutionService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    protected ProcessCardExecutionService $service;
    
    public function __construct(ProcessCardExecutionService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the Payment "created" event
     */
    public function created(Payment $payment): void
    {
        // Dispatch event for listener to handle
        event(new PaymentCreated($payment));
    }
    
    /**
     * Handle the Payment "updated" event
     */
    public function updated(Payment $payment): void
    {
        // Check for status change to posted (approved)
        if ($payment->wasChanged('status') && $payment->status === 'posted') {
            event(new PaymentApproved($payment));
        }
        
        // Check for settlement
        if ($payment->wasChanged('paid_at') && $payment->paid_at) {
            event(new PaymentSettled($payment));
            
            // Auto-execute on settlement if configured
            if ($payment->processCard && ($payment->processCard->rules['auto_execute_on_settlement'] ?? false)) {
                try {
                    $this->service->executeWithRetry($payment, $payment->processCard);
                } catch (\Exception $e) {
                    Log::error("ProcessCard execution failed on settlement", [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
    
    /**
     * Handle the Payment "deleting" event
     */
    public function deleting(Payment $payment): void
    {
        if (!$payment->processCard) {
            return;
        }
        
        $rules = $payment->processCard->rules;
        
        // Auto-reverse on deletion if configured
        if ($rules['reverse_on_rejection'] ?? false) {
            try {
                $this->service->reverseAccountingCycle($payment, 'Payment deleted');
                
                Log::info("ProcessCard auto-reversed on payment deletion", [
                    'payment_id' => $payment->id,
                    'process_card_id' => $payment->processCard->id
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to reverse ProcessCard on deletion", [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

