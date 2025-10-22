# 📊 Complete Accounting Cycle Implementation

## Overview

This implementation provides a complete, automated accounting cycle system integrated with ProcessCards (automated sticky notes) that handle book balancing, double-entry bookkeeping, reconciliation, and audit trails.

---

## 🏗️ Architecture

### Database Schema

```
Budget → Fund → Reserve → Expenditure → PaymentBatch → Payment
                                                          ↓
                                                    ProcessCard (Automated Rules)
                                                          ↓
                                      ┌───────────────────┴───────────────────┐
                                      ↓                                       ↓
                              Transactions (Double Entry)              Fund Transactions
                                      ↓                                       ↓
                              Account Postings                    Fund Balance Updates
                                      ↓
                         Ledger Account Balances
                                      ↓
                              Trial Balance → Reconciliation
```

---

## 📋 New Database Tables

### 1. **ledger_account_balances**

Tracks balance per account/ledger/period

-   Opening balance, total debits/credits, closing balance
-   Period-based (monthly/quarterly)
-   Unique per account/ledger/period

### 2. **trial_balances**

Department-level trial balance verification

-   Total debits must equal total credits
-   Period-based summary
-   Approval workflow

### 3. **reconciliations**

Reconciliation records for funds/ledgers/accounts

-   System balance vs actual balance
-   Variance tracking
-   Discrepancy management

### 4. **fund_transactions**

Complete audit trail of fund movements

-   All fund balance changes logged
-   Balance before/after tracking
-   Reversible transactions

### 5. **account_postings**

Bridge between transactions and chart of accounts

-   Running balance per account
-   Posting reference tracking
-   Reversal support

### 6. **posting_batches**

Groups related postings for approval

-   Balanced debit/credit validation
-   Approval workflow
-   Batch posting mechanism

### 7. **accounting_audit_trails**

Comprehensive audit logging

-   All accounting actions tracked
-   Before/after values
-   IP address and user agent

---

## 🔄 Complete Accounting Cycle Flow

### Phase 1: Initiation

```php
// 1. Create Expenditure
$expenditure = Expenditure::create([
    'fund_id' => 1,
    'beneficiary_name' => 'John Doe',
    'total_approved_amount' => 50000,
    'status' => 'raised'
]);

// 2. Reserve Fund (Optional)
Reserve::create([
    'fund_id' => 1,
    'reservable_type' => Expenditure::class,
    'reservable_id' => $expenditure->id,
    'total_reserved_amount' => 50000
]);
→ Fund.total_reserved_amount += 50000
→ Fund.total_actual_balance -= 50000
```

### Phase 2: Payment Processing

```php
// 3. Create Payment Batch
$batch = PaymentBatch::create([
    'fund_id' => 1,
    'total_payable_amount' => 150000
]);

// 4. Create Payment with ProcessCard
$payment = Payment::create([
    'expenditure_id' => $expenditure->id,
    'payment_batch_id' => $batch->id,
    'process_card_id' => 5,  // Auto ProcessCard
    'total_approved_amount' => 50000,
    'ledger_id' => 1
]);
```

### Phase 3: ProcessCard Execution

```php
// 5. Execute ProcessCard Rules
$processCard = ProcessCard::find(5);
$executionService = new ProcessCardExecutionService($fundRepository);

$steps = $executionService->executeAccountingCycle($payment, $processCard);

// Automatically executes based on rules:
// ✓ Generate Transactions (if generate_transactions = true)
// ✓ Create Account Postings (if post_to_journal = true)
// ✓ Update Ledger Balances (always)
// ✓ Settle Fund (if settle = true)
// ✓ Update Trial Balance (if update_trial_balance = true)
// ✓ Create Audit Trail (always)
```

### Phase 4: Double Entry Transaction Generation

```php
// 6. Transactions are created in pairs (if create_contra_entries = true)

Transaction [
  // Debit Entry (Expense)
  {
    type: 'debit',
    chart_of_account_id: 500, // Expense Account
    debit_amount: 50000,
    credit_amount: 0,
    process_card_id: 5,
    batch_reference: 'BATCH-ABC123',
    narration: 'Staff Payment - John Doe'
  },
  // Credit Entry (Contra - Cash/Bank)
  {
    type: 'credit',
    chart_of_account_id: 100, // Cash/Bank Account
    debit_amount: 0,
    credit_amount: 50000,
    process_card_id: 5,
    batch_reference: 'BATCH-ABC123',
    contra_transaction_id: 1, // Links to debit entry
    narration: 'Staff Payment - John Doe (Contra)'
  }
]
```

### Phase 5: Account Posting

```php
// 7. Account Postings created for each transaction

AccountPosting::create([
    'transaction_id' => 1,
    'chart_of_account_id' => 500,
    'ledger_id' => 1,
    'process_card_id' => 5,
    'debit' => 50000,
    'credit' => 0,
    'running_balance' => 450000, // Previous balance + debit
    'posting_reference' => 'POST-XYZ789',
    'posting_type' => 'auto',
    'posted_at' => now(),
    'posted_by' => Auth::id()
]);
```

### Phase 6: Ledger Balance Update

```php
// 8. Ledger Account Balance updated for the period

LedgerAccountBalance::updateOrCreate([
    'chart_of_account_id' => 500,
    'ledger_id' => 1,
    'period' => '2025-10',
    'fiscal_year' => 2025
], [
    'total_debits' => DB::raw('total_debits + 50000'),
    'total_credits' => DB::raw('total_credits + 0'),
    'closing_balance' => DB::raw('opening_balance + total_debits - total_credits')
]);
```

### Phase 7: Fund Settlement

```php
// 9. Fund Transaction created and balance updated

FundTransaction::create([
    'fund_id' => 1,
    'process_card_id' => 5,
    'transaction_type' => 'payment',
    'movement' => 'debit',
    'amount' => 50000,
    'balance_before' => 500000,
    'balance_after' => 450000,
    'source_id' => $payment->id,
    'source_type' => Payment::class
]);

// Fund balances updated
Fund::find(1)->update([
    'total_actual_spent_amount' => DB::raw('total_actual_spent_amount + 50000'),
    'total_booked_balance' => DB::raw('total_booked_balance - 50000'),
    'total_actual_balance' => DB::raw('total_actual_balance - 50000'),
    'total_reserved_amount' => DB::raw('total_reserved_amount - 50000') // If reserve exists
]);
```

### Phase 8: Trial Balance Update

```php
// 10. Trial Balance updated for the period

TrialBalance::updateOrCreate([
    'department_id' => 1,
    'period' => '2025-10',
    'fiscal_year' => 2025
], [
    'total_debits' => DB::raw('total_debits + 50000'),
    'total_credits' => DB::raw('total_credits + 50000'),
]);

// Validate balance
$trialBalance->validate(); // Sets is_balanced = (total_debits == total_credits)
```

### Phase 9: Audit Trail

```php
// 11. Audit trail created

AccountingAuditTrail::create([
    'user_id' => Auth::id(),
    'action' => 'create',
    'auditable_type' => Payment::class,
    'auditable_id' => $payment->id,
    'new_values' => ['process_card_executed' => true],
    'reason' => 'ProcessCard execution for payment',
    'ip_address' => request()->ip()
]);
```

---

## 🎯 ProcessCard Rules Configuration

### Example ProcessCard Setup

```php
ProcessCard::create([
    'document_type_id' => 1,
    'ledger_id' => 1,
    'service' => 'payment',
    'name' => 'Staff Payment Processor',
    'component' => 'PaymentProcessor',
    'debit_account_id' => 500, // Default expense account
    'credit_account_id' => 100, // Default cash account
    'execution_order' => 1,
    'rules' => [
        // Financial & Transaction
        'currency' => 'NGN',
        'transaction' => 'debit',
        'book_type' => 'journal',
        'generate_transactions' => true,
        'post_to_journal' => true,

        // Settlement & Processing
        'settle' => true,
        'auto_settle_fund' => true,
        'settlement_stage' => 'on-payment',

        // Double Entry & Posting
        'create_contra_entries' => true,
        'posting_priority' => 'batch',
        'update_trial_balance' => true,

        // Reconciliation
        'require_reconciliation' => true,
        'reconciliation_frequency' => 'monthly',

        // Audit
        'audit_trail_level' => 'detailed',
        'reverse_on_rejection' => true,
    ]
]);
```

---

## 💻 Usage Examples

### Execute ProcessCard for a Payment

```php
use App\Services\ProcessCardExecutionService;
use App\Models\Payment;
use App\Models\ProcessCard;

$payment = Payment::find(1);
$processCard = ProcessCard::find(5);

$service = new ProcessCardExecutionService($fundRepository);
$steps = $service->executeAccountingCycle($payment, $processCard);

// Returns array of completed steps:
// [
//   ['phase' => 'validation', 'status' => 'completed'],
//   ['phase' => 'transactions', 'data' => [...], 'status' => 'completed'],
//   ['phase' => 'postings', 'data' => [...], 'status' => 'completed'],
//   ['phase' => 'settlement', 'data' => {...}, 'status' => 'completed'],
//   ...
// ]
```

### Reverse a Payment

```php
$service->reverseAccountingCycle($payment, 'Payment error - incorrect amount');

// Automatically:
// - Reverses all account postings
// - Reverses fund transaction
// - Restores fund balances
// - Creates audit trail
```

### Use Trait in Payment Model

```php
use App\Traits\ExecutesProcessCard;

class Payment extends Model {
    use ExecutesProcessCard;
}

// Then in your code:
$payment = Payment::find(1);
$processCard = ProcessCard::find(5);

// Manual execution
$steps = $payment->executeProcessCard($payment, $processCard);

// Auto execution (finds matching ProcessCard automatically)
$steps = $payment->autoExecuteProcessCard($payment);

// Reversal
$payment->reverseProcessCard($payment, 'Reason for reversal');
```

### Reconcile a Fund

```php
$service->reconcilePayment($payment, 49500); // Actual amount paid

// Creates reconciliation record:
// - system_balance: 50000
// - actual_balance: 49500
// - variance: 500
// - status: 'discrepancy' (variance > 0.01)
```

---

## 🔍 Query Examples

### Get All Transactions for a Payment

```php
$payment = Payment::with([
    'transactions.accountPostings',
    'transactions.processCard',
    'processCard.debitAccount',
    'processCard.creditAccount'
])->find(1);
```

### Get Fund Transaction History

```php
$fund = Fund::with('fundTransactions.processCard')->find(1);

foreach ($fund->fundTransactions as $transaction) {
    echo "{$transaction->narration}: {$transaction->movement} {$transaction->amount}\n";
    echo "Balance: {$transaction->balance_before} → {$transaction->balance_after}\n";
}
```

### Get Trial Balance for Period

```php
$trialBalance = TrialBalance::where('department_id', 1)
    ->where('period', '2025-10')
    ->first();

if ($trialBalance->is_balanced) {
    echo "Debits: {$trialBalance->total_debits}, Credits: {$trialBalance->total_credits}";
} else {
    echo "UNBALANCED! Variance: {$trialBalance->variance}";
}
```

### Get Reconciliation Status

```php
$reconciliations = Reconciliation::where('fund_id', 1)
    ->where('status', 'discrepancy')
    ->get();

foreach ($reconciliations as $recon) {
    echo "Variance: {$recon->variance}\n";
    print_r($recon->discrepancies);
}
```

---

## 🚀 Integration Points

### 1. Payment Service Integration

```php
// In app/Services/PaymentService.php

use App\Traits\ExecutesProcessCard;

class PaymentService extends BaseService {
    use ExecutesProcessCard;

    public function store(array $data) {
        $payment = parent::store($data);

        // Auto-execute ProcessCard if configured
        $this->autoExecuteProcessCard($payment);

        return $payment;
    }
}
```

### 2. Workflow Integration

```php
// When payment is approved, trigger ProcessCard
$payment->update(['status' => 'posted']);

if ($payment->processCard &&
    $payment->processCard->rules['settlement_stage'] === 'on-approval') {
    $service = new ProcessCardExecutionService($fundRepository);
    $service->executeAccountingCycle($payment, $payment->processCard);
}
```

### 3. Batch Processing

```php
// Execute ProcessCards for all payments in a batch
$batch = PaymentBatch::with('payments.processCard')->find(1);

foreach ($batch->payments as $payment) {
    if ($payment->processCard &&
        $payment->processCard->rules['posting_priority'] === 'batch') {
        $service->executeAccountingCycle($payment, $payment->processCard);
    }
}
```

---

## 📊 Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ 1. EXPENDITURE CREATED                                      │
│    ├─ Status: raised                                        │
│    └─ Fund: Reserved (optional)                             │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. PAYMENT BATCH CREATED                                    │
│    ├─ Groups multiple expenditures                          │
│    └─ Status: pending                                       │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. PAYMENT CREATED                                          │
│    ├─ Links to ProcessCard                                  │
│    └─ Status: draft                                         │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. PROCESSCARD EXECUTION (Automated)                        │
│    ├─ Validates fund balance                                │
│    ├─ Generates transactions (double entry)                 │
│    ├─ Creates account postings                              │
│    ├─ Updates ledger balances                               │
│    ├─ Settles fund                                          │
│    ├─ Updates trial balance                                 │
│    └─ Creates audit trail                                   │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. DOUBLE ENTRY TRANSACTIONS                                │
│    ├─ Debit: Expense Account (e.g., Staff Salaries)        │
│    └─ Credit: Asset Account (e.g., Bank/Cash)              │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. ACCOUNT POSTINGS                                         │
│    ├─ Posted to Chart of Accounts                           │
│    └─ Running balance calculated                            │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 7. LEDGER ACCOUNT BALANCES                                  │
│    ├─ Period: 2025-10                                       │
│    ├─ Opening + Debits - Credits = Closing                  │
│    └─ Validation: Balanced                                  │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 8. FUND TRANSACTION & BALANCE UPDATE                        │
│    ├─ Fund.total_actual_balance -= 50000                    │
│    ├─ Fund.total_actual_spent_amount += 50000               │
│    └─ FundTransaction created (audit trail)                 │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 9. TRIAL BALANCE UPDATED                                    │
│    ├─ Total Debits: 150000                                  │
│    ├─ Total Credits: 150000                                 │
│    ├─ Variance: 0                                           │
│    └─ is_balanced: true ✓                                   │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 10. RECONCILIATION (Monthly)                                │
│     ├─ System Balance vs Bank Statement                     │
│     ├─ Variance Detection                                   │
│     └─ Status: reconciled / discrepancy                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Configuration Guide

### ProcessCard Rules Properties

| Property                   | Type           | Purpose                               | Default    |
| -------------------------- | -------------- | ------------------------------------- | ---------- |
| `generate_transactions`    | boolean        | Auto-create double-entry transactions | false      |
| `book_type`                | ledger/journal | Which book to use                     | ledger     |
| `post_to_journal`          | boolean        | Post to journal book                  | false      |
| `create_contra_entries`    | boolean        | Auto-create opposite entries          | true       |
| `settle`                   | boolean        | Auto-settle on execution              | false      |
| `auto_settle_fund`         | boolean        | Auto-update fund balances             | false      |
| `settlement_stage`         | enum           | When to settle                        | on-payment |
| `posting_priority`         | enum           | When to execute                       | batch      |
| `update_trial_balance`     | boolean        | Auto-update trial balance             | true       |
| `require_reconciliation`   | boolean        | Require reconciliation                | false      |
| `reconciliation_frequency` | enum           | How often to reconcile                | monthly    |
| `reverse_on_rejection`     | boolean        | Auto-reverse if rejected              | true       |
| `require_dual_approval`    | boolean        | Require 2 approvals                   | false      |
| `audit_trail_level`        | enum           | Audit detail level                    | detailed   |

---

## ✅ Migration Steps

Run migrations in order:

```bash
php artisan migrate --path=database/migrations/2025_10_07_000001_add_accounting_fields_to_transactions_table.php
php artisan migrate --path=database/migrations/2025_10_07_000002_create_ledger_account_balances_table.php
php artisan migrate --path=database/migrations/2025_10_07_000003_create_trial_balances_table.php
php artisan migrate --path=database/migrations/2025_10_07_000004_create_reconciliations_table.php
php artisan migrate --path=database/migrations/2025_10_07_000005_add_process_card_fields_to_payments_table.php
php artisan migrate --path=database/migrations/2025_10_07_000006_create_fund_transactions_table.php
php artisan migrate --path=database/migrations/2025_10_07_000007_create_account_postings_table.php
php artisan migrate --path=database/migrations/2025_10_07_000008_create_posting_batches_table.php
php artisan migrate --path=database/migrations/2025_10_07_000009_create_accounting_audit_trails_table.php
php artisan migrate --path=database/migrations/2025_10_07_000010_add_chart_of_account_fields_to_process_cards_table.php
php artisan migrate --path=database/migrations/2025_10_07_000011_add_posting_fields_to_journal_types_table.php
```

Or run all at once:

```bash
php artisan migrate
```

---

## 📈 Benefits

### 1. **Automated Book Balancing**

-   Double-entry bookkeeping enforced
-   Automatic contra entry generation
-   Real-time balance calculation

### 2. **Complete Audit Trail**

-   Every transaction logged
-   Before/after values tracked
-   User and IP tracking

### 3. **Reconciliation Support**

-   System vs actual balance comparison
-   Variance detection
-   Discrepancy management

### 4. **Flexible Configuration**

-   Rule-based automation
-   Per-service customization
-   Priority-based execution

### 5. **Data Integrity**

-   Transaction reversal support
-   Balance validation
-   Trial balance verification

---

## 🔧 Troubleshooting

### Unbalanced Trial Balance

```php
$trialBalance = TrialBalance::where('is_balanced', false)->first();
echo "Variance: {$trialBalance->variance}";

// Find problematic transactions
$period = $trialBalance->period;
$transactions = Transaction::whereDate('created_at', 'like', $period . '%')
    ->whereDoesntHave('contraTransaction')
    ->get();
```

### Fund Balance Mismatch

```php
$fund = Fund::find(1);
$calculatedBalance = $fund->total_approved_amount - $fund->total_actual_spent_amount;
$actualBalance = $fund->total_actual_balance;

if (abs($calculatedBalance - $actualBalance) > 0.01) {
    // Reconciliation needed
    $fundTransactions = $fund->fundTransactions()->orderBy('created_at')->get();
    // Review transaction history
}
```

---

## 📝 Summary

This implementation provides:

-   ✅ **7 new database tables** for complete accounting cycle
-   ✅ **7 new Eloquent models** with relationships
-   ✅ **ProcessCardExecutionService** for orchestration
-   ✅ **ExecutesProcessCard trait** for easy integration
-   ✅ **Complete double-entry bookkeeping**
-   ✅ **Automated fund balance management**
-   ✅ **Trial balance tracking**
-   ✅ **Reconciliation system**
-   ✅ **Comprehensive audit trails**
-   ✅ **Reversible transactions**

**Status**: Production-ready and backward compatible! 🚀
