<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialBalance extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'total_debits' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'variance' => 'decimal:2',
        'account_balances' => 'array',
        'ledger_summary' => 'array',
        'is_balanced' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Model Relationships
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper Methods
    public function calculateVariance(): float
    {
        return $this->total_debits - $this->total_credits;
    }

    public function isBalanced(): bool
    {
        return abs($this->calculateVariance()) < 0.01;
    }

    public function validate(): void
    {
        $variance = $this->calculateVariance();
        $this->variance = $variance;
        $this->is_balanced = abs($variance) < 0.01;
        $this->save();
    }
}

