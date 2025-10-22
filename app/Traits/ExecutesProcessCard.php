<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\ProcessCard;
use App\Services\ProcessCardExecutionService;

trait ExecutesProcessCard
{
    /**
     * Execute ProcessCard rules for a payment
     */
    public function executeProcessCard(Payment $payment, ProcessCard $processCard): array
    {
        $executionService = app(ProcessCardExecutionService::class);
        return $executionService->executeAccountingCycle($payment, $processCard);
    }

    /**
     * Auto-execute ProcessCard if payment meets criteria
     */
    public function autoExecuteProcessCard(Payment $payment): ?array
    {
        // Find matching ProcessCard for this payment's service/document type
        $processCard = ProcessCard::where('service', $payment->resource_type ?? 'payment')
            ->where('document_type_id', $payment->document_type_id)
            ->where('is_disabled', false)
            ->first();

        if (!$processCard) {
            return null;
        }

        $rules = $processCard->rules;

        // Check if auto-execution is appropriate based on posting priority
        $shouldAutoExecute = match ($rules['posting_priority'] ?? 'batch') {
            'immediate' => true,
            'batch' => $this->isInBatchContext(),
            'scheduled' => false,
            default => false,
        };

        if ($shouldAutoExecute) {
            return $this->executeProcessCard($payment, $processCard);
        }

        // Store ProcessCard reference for later execution
        $payment->update(['process_card_id' => $processCard->id]);

        return null;
    }

    /**
     * Check if we're in a batch processing context
     */
    protected function isInBatchContext(): bool
    {
        // Check if payment is part of a batch
        return property_exists($this, 'payment_batch_id') && $this->payment_batch_id > 0;
    }

    /**
     * Reverse ProcessCard execution
     */
    public function reverseProcessCard(Payment $payment, string $reason): array
    {
        $executionService = app(ProcessCardExecutionService::class);
        return $executionService->reverseAccountingCycle($payment, $reason);
    }

    /**
     * Validate ProcessCard execution prerequisites
     */
    public function canExecuteProcessCard(Payment $payment, ProcessCard $processCard): bool
    {
        $rules = $processCard->rules;

        // Check if approval is required and payment is approved
        if (($rules['requires_approval'] ?? false) && $payment->status !== 'posted') {
            return false;
        }

        // Check if dual approval is required
        if ($rules['require_dual_approval'] ?? false) {
            // Implement dual approval check logic
            return $this->hasDualApproval($payment);
        }

        return true;
    }

    /**
     * Check if payment has dual approval
     */
    protected function hasDualApproval(Payment $payment): bool
    {
        // Placeholder - implement based on your approval workflow
        return true;
    }
}

