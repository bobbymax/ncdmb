<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransaction extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'is_reversed' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    // Model Relationships
    public function fund(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function processCard(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProcessCard::class, 'process_card_id');
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    // Helper Methods
    public function reverse(User $user, ?string $reason = null): self
    {
        if ($this->is_reversed) {
            throw new \Exception('Transaction already reversed');
        }

        // Create reversal transaction
        $reversal = self::create([
            'fund_id' => $this->fund_id,
            'process_card_id' => $this->process_card_id,
            'reference' => 'REV-' . $this->reference,
            'transaction_type' => 'reversal',
            'movement' => $this->movement === 'debit' ? 'credit' : 'debit',
            'amount' => $this->amount,
            'balance_before' => $this->balance_after,
            'balance_after' => $this->balance_before,
            'source_id' => $this->source_id,
            'source_type' => $this->source_type,
            'narration' => 'Reversal: ' . $this->narration . ($reason ? ' - ' . $reason : ''),
            'created_by' => $user->id,
        ]);

        // Mark original as reversed
        $this->update([
            'is_reversed' => true,
            'reversed_by' => $user->id,
            'reversed_at' => now(),
        ]);

        return $reversal;
    }
}

