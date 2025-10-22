<?php

namespace App\Console\Commands;

use App\Models\LedgerAccountBalance;
use App\Models\ProcessCard;
use App\Models\TrialBalance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CloseAccountingPeriod extends Command
{
    protected $signature = 'accounting:close-period {period?} {--force}';
    protected $description = 'Close accounting period and validate balances';
    
    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $period = $this->argument('period') ?? now()->subMonth()->format('Y-m');
        $force = $this->option('force');
        
        $this->info("Closing accounting period: {$period}");
        
        if (!$force) {
            if (!$this->confirm('Are you sure you want to close this period? This action validates all balances.')) {
                $this->warn('Period closing cancelled');
                return 0;
            }
        }
        
        return DB::transaction(function () use ($period) {
            // 1. Validate all trial balances for the period
            $trialBalances = TrialBalance::where('period', $period)->get();
            
            $this->info("Validating {$trialBalances->count()} trial balance(s)...");
            
            foreach ($trialBalances as $trialBalance) {
                $trialBalance->validate();
                
                if (!$trialBalance->is_balanced) {
                    $this->error("  ❌ Department {$trialBalance->department_id} is UNBALANCED! Variance: {$trialBalance->variance}");
                    
                    if (!$this->option('force')) {
                        $this->error("Period closing aborted due to unbalanced trial balance");
                        return 1;
                    }
                } else {
                    $this->info("  ✅ Department {$trialBalance->department_id} is balanced");
                }
            }
            
            // 2. Close all ledger account balances for the period
            $balances = LedgerAccountBalance::where('period', $period)
                ->where('is_closed', false)
                ->get();
            
            $this->info("Closing {$balances->count()} ledger account balance(s)...");
            
            foreach ($balances as $balance) {
                if (!$balance->isBalanced()) {
                    $this->warn("  ⚠️  Account {$balance->chart_of_account_id} has balance discrepancy");
                }
                
                $balance->update([
                    'is_closed' => true,
                    'closed_at' => now(),
                    'closed_by' => 1, // System user
                ]);
            }
            
            // 3. Create opening balances for next period
            $nextPeriod = now()->parse($period)->addMonth()->format('Y-m');
            $this->info("Creating opening balances for {$nextPeriod}...");
            
            foreach ($balances as $balance) {
                LedgerAccountBalance::firstOrCreate([
                    'chart_of_account_id' => $balance->chart_of_account_id,
                    'ledger_id' => $balance->ledger_id,
                    'period' => $nextPeriod,
                    'fiscal_year' => now()->parse($period)->addMonth()->year,
                ], [
                    'department_id' => $balance->department_id,
                    'fund_id' => $balance->fund_id,
                    'opening_balance' => $balance->closing_balance,
                    'total_debits' => 0,
                    'total_credits' => 0,
                    'closing_balance' => $balance->closing_balance,
                ]);
            }
            
            $this->newLine();
            $this->info("✅ Period {$period} closed successfully!");
            $this->info("✅ Opening balances created for {$nextPeriod}");
            
            return 0;
        });
    }
}

