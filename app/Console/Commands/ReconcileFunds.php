<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\ProcessCard;
use App\Services\ProcessCardExecutionService;
use Illuminate\Console\Command;

class ReconcileFunds extends Command
{
    protected $signature = 'accounting:reconcile {frequency=daily}';
    protected $description = 'Auto-reconcile funds based on ProcessCard rules';
    
    /**
     * Execute the console command
     */
    public function handle(ProcessCardExecutionService $service): int
    {
        $frequency = $this->argument('frequency');
        
        $this->info("Starting {$frequency} reconciliation...");
        
        // Find all ProcessCards with matching reconciliation frequency
        $processCards = ProcessCard::where('is_disabled', false)
            ->get()
            ->filter(function ($card) use ($frequency) {
                $rules = $card->rules;
                return ($rules['require_reconciliation'] ?? false) && 
                       ($rules['reconciliation_frequency'] ?? 'monthly') === $frequency;
            });
        
        if ($processCards->isEmpty()) {
            $this->warn("No ProcessCards found with {$frequency} reconciliation frequency");
            return 0;
        }
        
        $this->info("Found {$processCards->count()} ProcessCard(s) to reconcile");
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($processCards as $processCard) {
            $this->line("Processing ProcessCard: {$processCard->name}");
            
            // Get settled payments that need reconciliation
            $payments = Payment::where('process_card_id', $processCard->id)
                ->where('is_settled', true)
                ->whereDoesntHave('reconciliations')
                ->get();
            
            foreach ($payments as $payment) {
                try {
                    // Use actual paid amount for reconciliation
                    $actualAmount = $payment->total_amount_paid ?? $payment->total_approved_amount;
                    $service->reconcilePayment($payment, $actualAmount);
                    
                    $successCount++;
                    $this->info("  ✅ Reconciled Payment #{$payment->id} ({$payment->code})");
                } catch (\Exception $e) {
                    $failCount++;
                    $this->error("  ❌ Failed to reconcile Payment #{$payment->id}: {$e->getMessage()}");
                }
            }
        }
        
        $this->newLine();
        $this->info("Reconciliation completed!");
        $this->info("✅ Success: {$successCount}");
        if ($failCount > 0) {
            $this->error("❌ Failed: {$failCount}");
        }
        
        return $failCount > 0 ? 1 : 0;
    }
}

