<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\ProcessCard;
use App\Services\ProcessCardExecutionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessBatchPostings extends Command
{
    protected $signature = 'accounting:process-batch {--time=23:00}';
    protected $description = 'Process batch priority ProcessCards at scheduled time';
    
    /**
     * Execute the console command
     */
    public function handle(ProcessCardExecutionService $service): int
    {
        $this->info("Processing batch priority ProcessCards...");
        
        // Find all ProcessCards with batch posting priority
        $processCards = ProcessCard::where('is_disabled', false)
            ->get()
            ->filter(function ($card) {
                return ($card->rules['posting_priority'] ?? 'batch') === 'batch';
            });
        
        if ($processCards->isEmpty()) {
            $this->warn("No batch priority ProcessCards found");
            return 0;
        }
        
        $this->info("Found {$processCards->count()} batch ProcessCard(s)");
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($processCards as $processCard) {
            $this->line("Processing: {$processCard->name}");
            
            // Get pending payments for this ProcessCard
            $payments = Payment::where('process_card_id', $processCard->id)
                ->where('status', 'draft')
                ->whereNull('process_metadata')
                ->get();
            
            foreach ($payments as $payment) {
                try {
                    $steps = $service->executeWithRetry($payment, $processCard);
                    
                    $successCount++;
                    $this->info("  ✅ Processed Payment #{$payment->id}");
                } catch (\Exception $e) {
                    $failCount++;
                    $this->error("  ❌ Failed Payment #{$payment->id}: {$e->getMessage()}");
                }
            }
        }
        
        $this->newLine();
        $this->info("Batch processing completed!");
        $this->info("✅ Success: {$successCount}");
        if ($failCount > 0) {
            $this->error("❌ Failed: {$failCount}");
        }
        
        return $failCount > 0 ? 1 : 0;
    }
}

