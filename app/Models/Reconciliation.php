<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'system_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'discrepancies' => 'array',
        'reconciled_at' => 'datetime',
    ];

    // Model Relationships
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function fund(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function ledger(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function reconciledBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    // Helper Methods
    public function calculateVariance(): float
    {
        return $this->system_balance - $this->actual_balance;
    }

    public function isReconciled(): bool
    {
        return $this->status === 'reconciled' && abs($this->variance) < 0.01;
    }

    public function hasDiscrepancy(): bool
    {
        return abs($this->variance) >= 0.01;
    }
}

