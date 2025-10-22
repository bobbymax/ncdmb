<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPosting extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'posted_at' => 'datetime',
        'is_reversed' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    // Model Relationships
    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function chartOfAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function ledger(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function processCard(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProcessCard::class, 'process_card_id');
    }

    public function postedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    // Helper Methods
    public function calculateRunningBalance(float $previousBalance): float
    {
        return $previousBalance + $this->debit - $this->credit;
    }

    public function reverse(User $user, string $reason): void
    {
        if ($this->is_reversed) {
            throw new \Exception('Posting already reversed');
        }

        $this->update([
            'is_reversed' => true,
            'reversed_by' => $user->id,
            'reversed_at' => now(),
            'reversal_reason' => $reason,
        ]);

        // Update ledger account balance
        $this->updateLedgerBalance($this->debit * -1, $this->credit * -1);
    }

    protected function updateLedgerBalance(float $debitAdjustment, float $creditAdjustment): void
    {
        $balance = LedgerAccountBalance::where([
            'chart_of_account_id' => $this->chart_of_account_id,
            'ledger_id' => $this->ledger_id,
            'period' => date('Y-m'),
            'fiscal_year' => date('Y'),
        ])->first();

        if ($balance) {
            $balance->total_debits += $debitAdjustment;
            $balance->total_credits += $creditAdjustment;
            $balance->closing_balance = $balance->calculateClosingBalance();
            $balance->save();
        }
    }
}

