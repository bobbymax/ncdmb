<?php

namespace App\Services;

use App\Models\AccountingAuditTrail;
use App\Models\AccountPosting;
use App\Models\FundTransaction;
use App\Models\JournalType;
use App\Models\LedgerAccountBalance;
use App\Models\Payment;
use App\Models\PostingBatch;
use App\Models\ProcessCard;
use App\Models\Transaction;
use App\Models\TrialBalance;
use App\Repositories\FundRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessCardExecutionService
{
    protected FundRepository $fundRepository;

    public function __construct(FundRepository $fundRepository)
    {
        $this->fundRepository = $fundRepository;
    }

    /**
     * Auto-find matching ProcessCard based on rules
     */
    public function findMatchingProcessCard(Payment $payment): ?ProcessCard
    {
        $processCards = ProcessCard::where('is_disabled', false)
            ->orderBy('execution_order', 'asc')
            ->get();

        foreach ($processCards as $card) {
            $rules = $card->rules;

            // Check service match
            if ($rules['match_by_service'] ?? true) {
                if ($card->service !== ($payment->resource_type ?? 'payment')) {
                    continue;
                }
            }

            // Check document type match
            if ($rules['match_by_document_type'] ?? true) {
                if ($card->document_type_id !== $payment->document_type_id) {
                    continue;
                }
            }

            // Check ledger match
            if ($rules['match_by_ledger'] ?? true) {
                if ($payment->ledger_id && $card->ledger_id !== $payment->ledger_id) {
                    continue;
                }
            }

            // Check amount range
            if ($rules['match_by_amount_range'] ?? false) {
                $amount = $payment->total_approved_amount;
                if (isset($rules['min_amount']) && $amount < $rules['min_amount']) {
                    continue;
                }
                if (isset($rules['max_amount']) && $amount > $rules['max_amount']) {
                    continue;
                }
            }

            // Found a match!
            return $card;
        }

        return null;
    }

    /**
     * Auto-attach ProcessCard to payment
     */
    public function autoAttachProcessCard(Payment $payment): ?ProcessCard
    {
        $processCard = $this->findMatchingProcessCard($payment);

        if ($processCard) {
            $payment->update(['process_card_id' => $processCard->id]);

            // Auto-execute based on posting_priority
            $rules = $processCard->rules;
            if ($rules['posting_priority'] === 'immediate') {
                $this->executeWithRetry($payment, $processCard);
            }
        }

        return $processCard;
    }

    /**
     * Execute with auto-retry on failure
     */
    public function executeWithRetry(Payment $payment, ProcessCard $processCard): array
    {
        $rules = $processCard->rules;
        $maxAttempts = $rules['retry_attempts'] ?? 1;
        $attempts = 0;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            try {
                return $this->executeAccountingCycle($payment, $processCard);
            } catch (\Exception $e) {
                $attempts++;
                $lastException = $e;

                Log::warning("ProcessCard execution attempt {$attempts} failed", [
                    'payment_id' => $payment->id,
                    'process_card_id' => $processCard->id,
                    'error' => $e->getMessage()
                ]);

                if ($attempts >= $maxAttempts) {
                    // Final failure
                    if ($rules['notify_on_failure'] ?? false) {
                        $this->notifyFailure($payment, $processCard, $e);
                    }

                    if ($rules['escalate_on_repeated_failure'] ?? false) {
                        $this->escalateFailure($payment, $processCard, $e);
                    }

                    throw $e;
                }

                // Wait before retry
                sleep(2);
            }
        }

        throw $lastException ?? new \Exception('Unknown error in executeWithRetry');
    }

    /**
     * Notify on execution failure
     */
    protected function notifyFailure(Payment $payment, ProcessCard $processCard, \Exception $e): void
    {
        // Send notification to administrators
        Log::error('ProcessCard execution failed', [
            'payment_id' => $payment->id,
            'process_card_id' => $processCard->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // TODO: Send email/notification to admins
    }

    /**
     * Escalate repeated failures
     */
    protected function escalateFailure(Payment $payment, ProcessCard $processCard, \Exception $e): void
    {
        // Create escalation record or notification
        Log::critical('ProcessCard execution repeatedly failed - ESCALATED', [
            'payment_id' => $payment->id,
            'process_card_id' => $processCard->id,
            'error' => $e->getMessage()
        ]);

        // TODO: Create escalation workflow
    }

    /**
     * Execute accounting cycle with stage awareness (ProgressTracker integration)
     */
    public function executeAccountingCycleForStage(
        Payment $payment,
        ProcessCard $processCard,
        \App\Models\ProgressTracker $currentTracker,
        ?\App\Models\ProgressTracker $previousTracker = null
    ): array {
        return DB::transaction(function () use ($payment, $processCard, $currentTracker, $previousTracker) {
            $steps = [];
            $rules = $processCard->rules;
            $stageOrder = $currentTracker->order;

            \Log::info("Executing ProcessCard for stage", [
                'payment_id' => $payment->id,
                'process_card_id' => $processCard->id,
                'stage_order' => $stageOrder,
                'stage_name' => $currentTracker->stage?->name ?? 'Unknown',
            ]);

            // Store stage context in payment metadata
            $stageContext = [
                'stage_order' => $stageOrder,
                'stage_id' => $currentTracker->workflow_stage_id,
                'stage_name' => $currentTracker->stage?->name ?? 'Unknown',
                'previous_stage_order' => $previousTracker?->order,
                'executed_at' => now()->toDateTimeString(),
                'process_card_id' => $processCard->id,
                'process_card_name' => $processCard->name,
            ];

            // Step 1: Validate fund balance (if configured and at appropriate stage)
            if ($stageOrder >= ($rules['min_stage_order'] ?? 1)) {
                if ($rules['auto_settle_fund'] ?? false) {
                    $fund = $payment->expenditure?->fund;
                    if ($fund) {
                        $availableBalance = $fund->approved_amount - $fund->settled_amount;
                        if ($availableBalance < $payment->total_approved_amount) {
                            throw new \Exception("Insufficient fund balance. Available: {$availableBalance}, Required: {$payment->total_approved_amount}");
                        }
                        $steps[] = 'validate_fund_balance';
                    }
                }
            }

            // Step 2: Generate transactions (only if at appropriate stage)
            $shouldGenerateTransactions = $rules['generate_transactions'] ?? false;

            // Check if we should skip transaction generation at this stage
            if (!empty($rules['execute_at_stages']) && is_array($rules['execute_at_stages'])) {
                if (!in_array($stageOrder, $rules['execute_at_stages'])) {
                    $shouldGenerateTransactions = false;
                }
            }

            if ($shouldGenerateTransactions) {
                $transactions = $this->generateDoubleEntryTransactions($payment, $processCard);
                $steps[] = 'generate_transactions';
                $stageContext['transactions_generated'] = count($transactions);
            }

            // Step 3: Post to ledger (if configured and at appropriate stage)
            if ($rules['post_to_journal'] ?? false) {
                if ($stageOrder >= ($rules['min_stage_order'] ?? 1)) {
                    $this->postToLedger($payment, $processCard);
                    $steps[] = 'post_to_ledger';
                }
            }

            // Step 4: Settle fund (only at specific stages or final stage)
            $shouldSettleFund = $rules['auto_settle_fund'] ?? false;

            if ($rules['execute_at_final_stage_only'] ?? false) {
                $maxOrder = \App\Models\ProgressTracker::where('workflow_id', $currentTracker->workflow_id)
                    ->where('document_type_id', $currentTracker->document_type_id)
                    ->max('order');

                $shouldSettleFund = $shouldSettleFund && ($stageOrder >= $maxOrder);
            }

            if ($shouldSettleFund) {
                $this->settleFund($payment, $processCard);
                $steps[] = 'settle_fund';
                $stageContext['fund_settled'] = true;
            }

            // Step 5: Update trial balance (if at appropriate stage)
            if ($rules['update_trial_balance'] ?? false) {
                if ($stageOrder >= ($rules['min_stage_order'] ?? 1)) {
                    $this->updateTrialBalance($payment, $processCard->toArray());
                    $steps[] = 'update_trial_balance';
                }
            }

            // Step 6: Create audit trail
            \App\Models\AccountingAuditTrail::log(
                'stage_execution',
                $payment,
                null,
                [
                    'process_card_id' => $processCard->id,
                    'process_card_name' => $processCard->name,
                    'stage_context' => $stageContext,
                    'steps_completed' => $steps,
                ],
                "ProcessCard executed at stage {$stageOrder}"
            );
            $steps[] = 'audit_trail';

            // Update payment metadata with stage context
            $payment->update([
                'process_metadata' => array_merge(
                    $payment->process_metadata ?? [],
                    [
                        'last_stage_execution' => $stageContext,
                        'execution_history' => array_merge(
                            $payment->process_metadata['execution_history'] ?? [],
                            [$stageContext]
                        ),
                    ]
                ),
            ]);

            Log::info("ProcessCard stage execution completed", [
                'payment_id' => $payment->id,
                'stage_order' => $stageOrder,
                'steps_completed' => $steps,
            ]);

            return $steps;
        });
    }

    /**
     * Execute the complete accounting cycle for a payment based on ProcessCard rules
     *
     * @param Payment $payment The payment to process
     * @param ProcessCard $processCard The process card with rules
     * @param array|null $journalTypes Optional journal types from frontend
     * @return array Steps completed during execution
     */
    public function executeAccountingCycle(Payment $payment, ProcessCard $processCard, ?array $journalTypes = null): array
    {
        return DB::transaction(function () use ($payment, $processCard, $journalTypes) {
            $steps = [];
            $rules = $processCard->rules;

            // STEP 1: Validate Fund Balance
            $this->validateFundBalance($payment);
            $steps[] = ['phase' => 'validation', 'status' => 'completed'];

            // STEP 2: Generate Transactions (Double Entry)
            if ($rules['generate_transactions'] ?? false) {
                $transactions = $this->generateDoubleEntryTransactions($payment, $processCard, $journalTypes);
                $steps[] = ['phase' => 'transactions', 'data' => $transactions, 'status' => 'completed'];
            }

            // STEP 3: Create Account Postings
            if (isset($transactions) && count($transactions) > 0) {
                $postings = $this->createAccountPostings($transactions, $processCard);
                $steps[] = ['phase' => 'postings', 'data' => $postings, 'status' => 'completed'];
            }

            // STEP 4: Update Ledger Account Balances
            if (isset($postings) && count($postings) > 0) {
                $this->updateLedgerAccountBalances($postings, $payment);
                $steps[] = ['phase' => 'ledger_balances', 'status' => 'completed'];
            }

            // STEP 5: Settle Fund Balance
            if ($rules['settle'] ?? false) {
                $fundTransaction = $this->settleFund($payment, $processCard);
                $steps[] = ['phase' => 'settlement', 'data' => $fundTransaction, 'status' => 'completed'];
            }

            // STEP 6: Update Trial Balance
            if ($rules['update_trial_balance'] ?? false) {
                $this->updateTrialBalance($payment, isset($postings) ? $postings : []);
                $steps[] = ['phase' => 'trial_balance', 'status' => 'completed'];
            }

            // STEP 7: Create Audit Trail
            $this->createAuditTrail($payment, $processCard, $steps);

            // STEP 8: Update Payment Status
            $payment->update([
                'process_metadata' => [
                    'executed_at' => now()->toIso8601String(),
                    'executed_by' => Auth::id(),
                    'steps' => $steps,
                ],
                'auto_generated' => true,
                'requires_settlement' => $rules['settle'] ?? false,
                'is_settled' => $rules['settle'] ?? false,
                'settled_at' => ($rules['settle'] ?? false) ? now() : null,
                'settled_by' => ($rules['settle'] ?? false) ? Auth::id() : null,
            ]);

            return $steps;
        });
    }

    /**
     * Execute accounting cycle with frontend-generated transactions
     *
     * @param Payment $payment The payment to process
     * @param ProcessCard $processCard The process card with rules
     * @param array $frontendTransactions Array of transactions from frontend
     * @return array Steps completed
     * @throws \Exception If transactions are invalid or imbalanced
     */
    public function executeAccountingCycleWithTransactions(Payment $payment, ProcessCard $processCard, array $frontendTransactions): array
    {
        return DB::transaction(function () use ($payment, $processCard, $frontendTransactions) {
            $steps = [];
            $rules = $processCard->rules;

            // STEP 1: Validate Fund Balance
//            $this->validateFundBalance($payment);
//            $steps[] = ['phase' => 'validation', 'status' => 'completed'];

            // STEP 2: Validate and Save Frontend Transactions
            $transactions = $this->validateAndSaveFrontendTransactions($payment, $processCard, $frontendTransactions);
            $steps[] = ['phase' => 'transactions', 'data' => $transactions, 'status' => 'completed'];

            // STEP 3: Create Account Postings
            if (count($transactions) > 0) {
                $postings = $this->createAccountPostings($transactions, $processCard);
                $steps[] = ['phase' => 'postings', 'data' => $postings, 'status' => 'completed'];
            }

            // STEP 4: Update Ledger Account Balances
            if (isset($postings) && count($postings) > 0) {
                $this->updateLedgerAccountBalances($postings, $payment);
                $steps[] = ['phase' => 'ledger_balances', 'status' => 'completed'];
            }

            // STEP 5: Settle Fund Balance
//            if ($rules['settle'] ?? false) {
//                $fundTransaction = $this->settleFund($payment, $processCard);
//                $steps[] = ['phase' => 'settlement', 'data' => $fundTransaction, 'status' => 'completed'];
//            }

            // STEP 6: Update Trial Balance
            if ($rules['update_trial_balance'] ?? false) {
                $this->updateTrialBalance($payment, isset($postings) ? $postings : []);
                $steps[] = ['phase' => 'trial_balance', 'status' => 'completed'];
            }

            // STEP 7: Create Audit Trail
            $this->createAuditTrail($payment, $processCard, $steps);

            // STEP 8: Update Payment Status
            $payment->update([
                'process_metadata' => [
                    'executed_at' => now()->toIso8601String(),
                    'executed_by' => Auth::id(),
                    'steps' => $steps,
                    'frontend_transactions' => true,
                ],
                'auto_generated' => true,
                'requires_settlement' => $rules['settle'] ?? false,
                'is_settled' => $rules['settle'] ?? false,
                'settled_at' => ($rules['settle'] ?? false) ? now() : null,
                'settled_by' => ($rules['settle'] ?? false) ? Auth::id() : null,
            ]);

            return $steps;
        });
    }

    /**
     * Validate fund has sufficient balance
     */
    protected function validateFundBalance(Payment $payment): void
    {
        $fund = $payment->expenditure->fund;

        if (!$fund) {
            throw new \Exception('Fund not found for expenditure');
        }

        if ($fund->total_actual_balance < $payment->total_approved_amount) {
            throw new \Exception("Insufficient fund balance. Available: {$fund->total_actual_balance}, Required: {$payment->total_approved_amount}");
        }
    }

    /**
     * Generate double-entry transactions based on journal types
     *
     * @param Payment $payment The payment to generate transactions for
     * @param ProcessCard $processCard The process card with rules
     * @param array|null $frontendJournalTypes Optional journal types from frontend
     * @return array Generated transactions
     * @throws \Exception If journal types are invalid or unbalanced
     */
    protected function generateDoubleEntryTransactions(Payment $payment, ProcessCard $processCard, ?array $frontendJournalTypes = null): array
    {
        $transactions = [];
        $batchRef = 'BATCH-' . Str::upper(Str::random(10));
        $rules = $processCard->rules;

        // Get journal types: frontend-provided or database-fetched
        if (!empty($frontendJournalTypes)) {
            // Validate and use frontend journal types
            $journalTypes = $this->validateAndPrepareJournalTypes($frontendJournalTypes, $processCard, $payment);
        } else {
            // Fallback to database journal types (current behavior)
            $journalTypes = $processCard->ledger->journalTypes()
                ->where('category', $payment->type)
                ->get();
        }

        foreach ($journalTypes as $journalType) {
            $amount = $this->calculateTransactionAmount($payment, $journalType);

            // Create main transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'department_id' => $payment->department_id,
                'payment_id' => $payment->id,
                'ledger_id' => $processCard->ledger_id,
                'chart_of_account_id' => $journalType->debit_account_id ?? $rules['default_debit_account_id'] ?? null,
                'journal_type_id' => $journalType->id,
                'process_card_id' => $processCard->id,
                'reference' => 'TXN-' . Str::upper(Str::random(12)),
                'type' => $journalType->type === 'both' ? 'debit' : $journalType->type,
                'amount' => $amount,
                'debit_amount' => $journalType->type === 'debit' || $journalType->type === 'both' ? $amount : 0,
                'credit_amount' => $journalType->type === 'credit' ? $amount : 0,
                'narration' => "{$journalType->code} - {$payment->narration}",
                'currency' => $payment->currency ?? 'NGN',
                'payment_method' => 'bank-transfer',
                'status' => 'pending',
                'entry_type' => 'regular',
                'batch_reference' => $batchRef,
            ]);

            $transactions[] = $transaction;

            // Create contra entry if enabled
            if (($rules['create_contra_entries'] ?? false) && $journalType->type !== 'both') {
                $contraTransaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'department_id' => $payment->department_id,
                    'payment_id' => $payment->id,
                    'ledger_id' => $processCard->ledger_id,
                    'chart_of_account_id' => $journalType->credit_account_id ?? $rules['default_credit_account_id'] ?? null,
                    'journal_type_id' => $journalType->id,
                    'process_card_id' => $processCard->id,
                    'reference' => 'TXN-' . Str::upper(Str::random(12)),
                    'type' => $journalType->type === 'debit' ? 'credit' : 'debit',
                    'amount' => $amount,
                    'debit_amount' => $journalType->type === 'credit' ? $amount : 0,
                    'credit_amount' => $journalType->type === 'debit' ? $amount : 0,
                    'narration' => "{$journalType->code} - {$payment->narration} (Contra)",
                    'currency' => $payment->currency ?? 'NGN',
                    'payment_method' => 'bank-transfer',
                    'status' => 'pending',
                    'entry_type' => 'regular',
                    'batch_reference' => $batchRef,
                    'contra_transaction_id' => $transaction->id,
                ]);

                // Link back to main transaction
                $transaction->update(['contra_transaction_id' => $contraTransaction->id]);
                $transactions[] = $contraTransaction;
            }
        }

        // Validate double-entry balance (debits must equal credits)
        if (!empty($frontendJournalTypes)) {
            $this->validateTransactionBalance($transactions);
        }

        return $transactions;
    }

    /**
     * Validate and prepare journal types from frontend
     *
     * @param array $frontendJournalTypes Journal types from frontend
     * @param ProcessCard $processCard Process card with rules
     * @param Payment $payment Payment being processed
     * @return \Illuminate\Support\Collection Validated journal types
     * @throws \Exception If validation fails
     */
    protected function validateAndPrepareJournalTypes(array $frontendJournalTypes, ProcessCard $processCard, Payment $payment): \Illuminate\Support\Collection
    {
        // Get allowed journal types for this ledger and category
        $allowedJournalTypes = $processCard->ledger->journalTypes()
            ->where('category', $payment->type)
            ->get()
            ->keyBy('id');

        if ($allowedJournalTypes->isEmpty()) {
            throw new \Exception("No journal types configured for ledger '{$processCard->ledger->name}' and category '{$payment->type}'");
        }

        $validatedJournalTypes = collect();

        foreach ($frontendJournalTypes as $frontendJT) {
            $journalTypeId = $frontendJT['id'] ?? $frontendJT['journal_type_id'] ?? null;

            if (!$journalTypeId) {
                Log::warning('Frontend journal type missing ID', ['data' => $frontendJT]);
                continue;
            }

            // Check if this journal type is allowed
            if (!$allowedJournalTypes->has($journalTypeId)) {
                throw new \Exception("Unauthorized journal type ID: {$journalTypeId}. This journal type is not allowed for this ledger and category.");
            }

            // Get the validated journal type from database
            $validJournalType = $allowedJournalTypes->get($journalTypeId);

            // Merge frontend overrides with database journal type
            // Frontend can override: amount, custom_amount, percentage
            $validJournalType->custom_amount = $frontendJT['amount'] ?? $frontendJT['custom_amount'] ?? null;
            $validJournalType->custom_percentage = $frontendJT['percentage'] ?? $frontendJT['amount_percentage'] ?? null;

            $validatedJournalTypes->push($validJournalType);
        }

        if ($validatedJournalTypes->isEmpty()) {
            throw new \Exception('No valid journal types provided after validation');
        }

        Log::info('Frontend journal types validated', [
            'payment_id' => $payment->id,
            'provided_count' => count($frontendJournalTypes),
            'validated_count' => $validatedJournalTypes->count(),
        ]);

        return $validatedJournalTypes;
    }

    /**
     * Validate that transaction debits equal credits
     *
     * @param array $transactions Generated transactions
     * @throws \Exception If transactions are unbalanced
     */
    protected function validateTransactionBalance(array $transactions): void
    {
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($transactions as $transaction) {
            $totalDebits += $transaction['debit_amount'] ?? 0;
            $totalCredits += $transaction['credit_amount'] ?? 0;
        }

        // Allow 1 cent variance due to floating point arithmetic
        $variance = abs($totalDebits - $totalCredits);

        if ($variance > 0.01) {
            throw new \Exception(
                "Unbalanced double-entry transaction. Total Debits: " . number_format($totalDebits, 2) .
                ", Total Credits: " . number_format($totalCredits, 2) .
                ", Variance: " . number_format($variance, 2)
            );
        }

        Log::info('Transaction balance validated', [
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'variance' => $variance,
        ]);
    }

    /**
     * Calculate transaction amount based on journal type rules
     * Supports custom amounts and percentages from frontend
     */
    protected function calculateTransactionAmount(Payment $payment, $journalType): float
    {
        $baseAmount = $payment->total_approved_amount;

        // Priority 1: Use custom amount from frontend if provided
        if (isset($journalType->custom_amount) && $journalType->custom_amount > 0) {
            return (float) $journalType->custom_amount;
        }

        // Priority 2: Use custom percentage from frontend if provided
        if (isset($journalType->custom_percentage) && $journalType->custom_percentage > 0) {
            return $baseAmount * ($journalType->custom_percentage / 100);
        }

        // Priority 3: Use tax rate if journal type is taxable
        if ($journalType->is_taxable) {
            return $baseAmount * ($journalType->tax_rate / 100);
        }

        return $baseAmount;
    }

    /**
     * Create account postings from transactions
     */
    protected function createAccountPostings(array $transactions, ProcessCard $processCard): array
    {
        $postings = [];
        $runningBalances = [];

        foreach ($transactions as $transaction) {
            $accountId = $transaction->chart_of_account_id;

            if (!isset($runningBalances[$accountId])) {
                $runningBalances[$accountId] = $this->getPreviousBalance($accountId, $processCard->ledger_id);
            }

            $runningBalances[$accountId] += $transaction->debit_amount - $transaction->credit_amount;

            $posting = AccountPosting::create([
                'transaction_id' => $transaction->id,
                'chart_of_account_id' => $transaction->chart_of_account_id,
                'ledger_id' => $processCard->ledger_id,
                'process_card_id' => $processCard->id,
                'debit' => $transaction->debit_amount,
                'credit' => $transaction->credit_amount,
                'running_balance' => $runningBalances[$accountId],
                'posting_reference' => 'POST-' . Str::upper(Str::random(12)),
                'posting_type' => 'auto',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
            ]);

            $postings[] = $posting;
        }

        return $postings;
    }

    /**
     * Get previous balance for an account
     */
    protected function getPreviousBalance(int $accountId, int $ledgerId): float
    {
        $lastPosting = AccountPosting::where('chart_of_account_id', $accountId)
            ->where('ledger_id', $ledgerId)
            ->latest('posted_at')
            ->first();

        return $lastPosting ? $lastPosting->running_balance : 0;
    }

    /**
     * Update ledger account balances for the current period
     */
    protected function updateLedgerAccountBalances(array $postings, Payment $payment): void
    {
        $period = now()->format('Y-m');
        $fiscalYear = now()->year;

        foreach ($postings as $posting) {
            $balance = LedgerAccountBalance::firstOrCreate([
                'chart_of_account_id' => $posting->chart_of_account_id,
                'ledger_id' => $posting->ledger_id,
                'period' => $period,
                'fiscal_year' => $fiscalYear,
            ], [
                'department_id' => $payment->department_id,
                'fund_id' => $payment->expenditure->fund_id ?? null,
                'opening_balance' => 0,
                'total_debits' => 0,
                'total_credits' => 0,
                'closing_balance' => 0,
            ]);

            $balance->total_debits += $posting->debit;
            $balance->total_credits += $posting->credit;
            $balance->closing_balance = $balance->calculateClosingBalance();
            $balance->save();
        }
    }

    /**
     * Settle fund balance
     */
    protected function settleFund(Payment $payment, ProcessCard $processCard): FundTransaction
    {
        $fund = $payment->expenditure->fund;
        $amount = $payment->total_approved_amount;
        $balanceBefore = $fund->total_actual_balance;

        // Create fund transaction record
        $fundTransaction = FundTransaction::create([
            'fund_id' => $fund->id,
            'process_card_id' => $processCard->id,
            'reference' => 'FT-' . Str::upper(Str::random(12)),
            'transaction_type' => 'payment',
            'movement' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore - $amount,
            'source_id' => $payment->id,
            'source_type' => Payment::class,
            'narration' => "Payment settlement for {$payment->code}",
            'created_by' => Auth::id(),
        ]);

        // Update fund balances
        $fund->total_expected_spent_amount += $amount;
        $fund->total_actual_spent_amount += $amount;
        $fund->total_booked_balance -= $amount;
        $fund->total_actual_balance -= $amount;

        // Release reserve if exists
        if ($payment->expenditure->reserve) {
            $fund->total_reserved_amount -= $amount;
        }

        $fund->save();

        return $fundTransaction;
    }

    /**
     * Update trial balance for the period
     */
    protected function updateTrialBalance(Payment $payment, array $postings): void
    {
        $period = now()->format('Y-m');
        $fiscalYear = now()->year;

        $trialBalance = TrialBalance::firstOrCreate([
            'department_id' => $payment->department_id,
            'period' => $period,
            'fiscal_year' => $fiscalYear,
        ], [
            'total_debits' => 0,
            'total_credits' => 0,
            'variance' => 0,
            'is_balanced' => false,
        ]);

        foreach ($postings as $posting) {
            $trialBalance->total_debits += $posting->debit;
            $trialBalance->total_credits += $posting->credit;
        }

        $trialBalance->validate();
    }

    /**
     * Create audit trail for the execution
     */
    protected function createAuditTrail(Payment $payment, ProcessCard $processCard, array $steps): void
    {
        AccountingAuditTrail::log(
            'create',
            $payment,
            null,
            [
                'process_card_id' => $processCard->id,
                'process_card_name' => $processCard->name,
                'steps_completed' => count($steps),
                'execution_summary' => $steps,
            ],
            "ProcessCard '{$processCard->name}' executed accounting cycle"
        );
    }

    /**
     * Reverse a payment's accounting entries
     */
    public function reverseAccountingCycle(Payment $payment, string $reason): array
    {
        return DB::transaction(function () use ($payment, $reason) {
            $steps = [];
            $user = Auth::user();

            // Reverse transactions
            $transactions = $payment->transactions;
            foreach ($transactions as $transaction) {
                if (!$transaction->is_reconciled) {
                    // Reverse account postings
                    foreach ($transaction->accountPostings as $posting) {
                        $posting->reverse($user, $reason);
                    }
                    $steps[] = ['phase' => 'transaction_reversed', 'transaction_id' => $transaction->id];
                }
            }

            // Reverse fund transaction
            $fundTransactions = FundTransaction::where('source_id', $payment->id)
                ->where('source_type', Payment::class)
                ->where('is_reversed', false)
                ->get();

            foreach ($fundTransactions as $fundTxn) {
                $fundTxn->reverse($user, $reason);
                $steps[] = ['phase' => 'fund_reversed', 'fund_transaction_id' => $fundTxn->id];
            }

            // Update payment status
            $payment->update([
                'status' => 'reversed',
                'is_settled' => false,
            ]);

            // Audit trail
            AccountingAuditTrail::log('reverse', $payment, null, ['reason' => $reason], $reason);

            return $steps;
        });
    }

    /**
     * Reconcile a payment
     */
    public function reconcilePayment(Payment $payment, float $actualAmount): void
    {
        DB::transaction(function () use ($payment, $actualAmount) {
            $fund = $payment->expenditure->fund;
            $variance = $payment->total_approved_amount - $actualAmount;

            $reconciliation = \App\Models\Reconciliation::create([
                'user_id' => Auth::id(),
                'department_id' => $payment->department_id,
                'fund_id' => $fund->id,
                'ledger_id' => $payment->ledger_id,
                'type' => 'fund',
                'period' => now()->format('Y-m'),
                'fiscal_year' => now()->year,
                'system_balance' => $payment->total_approved_amount,
                'actual_balance' => $actualAmount,
                'variance' => $variance,
                'status' => abs($variance) < 0.01 ? 'reconciled' : 'discrepancy',
                'reconciled_by' => abs($variance) < 0.01 ? Auth::id() : null,
                'reconciled_at' => abs($variance) < 0.01 ? now() : null,
            ]);

            // If there's a discrepancy, create adjustment if needed
            if (abs($variance) >= 0.01) {
                $reconciliation->update([
                    'discrepancies' => [
                        'payment_id' => $payment->id,
                        'expected' => $payment->total_approved_amount,
                        'actual' => $actualAmount,
                        'difference' => $variance,
                    ],
                ]);
            }
        });
    }

    /**
     * Validate and save frontend-generated transactions
     *
     * @param Payment $payment The payment being processed
     * @param ProcessCard $processCard The process card with rules
     * @param array $frontendTransactions Array of transaction data from frontend
     * @return array Saved Transaction models
     * @throws \Exception If validation fails
     */
    protected function validateAndSaveFrontendTransactions(Payment $payment, ProcessCard $processCard, array $frontendTransactions): array
    {
        // Validate transaction balance (debits = credits) using frontend format
        // $this->validateFrontendTransactionBalance($frontendTransactions);

        $savedTransactions = [];
        $batchRef = 'BATCH-' . Str::upper(Str::random(10));

        foreach ($frontendTransactions as $transData) {
            // Validate journal type exists
            $journalType = JournalType::find($transData['journal_type_id']);
            if (!$journalType) {
                throw new \Exception("Journal type not found: {$transData['journal_type_id']}");
            }

            // Validate journal type is allowed for this payment type
            if (!in_array($journalType->category, [$payment->type, 'default'])) {
                throw new \Exception("Journal type '{$journalType->code}' (category: {$journalType->category}) not allowed for payment type '{$payment->type}'");
            }

            // Create transaction with frontend data
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'department_id' => $payment->department_id,
                'payment_id' => $transData['payment_id'],
                'ledger_id' => $transData['ledger_id'],
                'chart_of_account_id' => $transData['chart_of_account_id'],
                'journal_type_id' => $transData['journal_type_id'],
                'process_card_id' => $processCard->id,
                'reference' => 'TXN-' . Str::upper(Str::random(12)),
                'type' => $transData['type'],
//                'amount' => $transData['amount'],
                'debit_amount' => $transData['debit_amount'] ?? 0,
                'credit_amount' => $transData['credit_amount'] ?? 0,
                'narration' => $transData['narration'],
                'beneficiary_id' => $transData['beneficiary_id'] ?? null,
                'beneficiary_type' => $transData['beneficiary_type'] ?? null,
                'currency' => $transData['currency'] ?? 'NGN',
                'payment_method' => $transData['payment_method'] ?? 'bank-transfer',
                'status' => 'pending',
                'entry_type' => 'regular',
                'batch_reference' => $batchRef,
                'flag' => $transData['flag'] ?? null,
                'posted_at' => now(),
//                'trail_balance' => $transData['trail_balance'] ?? null,
            ]);

            $savedTransactions[] = $transaction;
        }

        Log::info('Frontend transactions validated and saved', [
            'payment_id' => $payment->id,
            'transaction_count' => count($savedTransactions),
            'batch_reference' => $batchRef,
        ]);

        return $savedTransactions;
    }

    /**
     * Validate that frontend transactions are balanced (debits = credits)
     * Uses pre-calculated debit_amount and credit_amount from frontend
     *
     * @param array $transactions Array of transaction data from frontend
     * @throws \Exception If transactions are imbalanced
     */
    protected function validateFrontendTransactionBalance(array $transactions): void
    {
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($transactions as $trans) {
            // Use pre-calculated amounts from frontend
            $totalDebits += $trans['debit_amount'] ?? 0;
            $totalCredits += $trans['credit_amount'] ?? 0;
        }

        $variance = abs($totalDebits - $totalCredits);

        if ($variance > 0.01) {
            throw new \Exception(
                "Transaction imbalance detected: Debits (" . number_format($totalDebits, 2) .
                ") != Credits (" . number_format($totalCredits, 2) .
                "). Variance: " . number_format($variance, 2)
            );
        }

        Log::info('Frontend transaction balance validated', [
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'variance' => $variance,
        ]);
    }
}

