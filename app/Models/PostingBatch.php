<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostingBatch extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'total_debits' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'is_balanced' => 'boolean',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    // Model Relationships
    public function processCard(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProcessCard::class, 'process_card_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function postedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Helper Methods
    public function validate(): void
    {
        $this->is_balanced = abs($this->total_debits - $this->total_credits) < 0.01;
        $this->save();
    }

    public function canPost(): bool
    {
        return $this->is_balanced && 
               $this->status === 'approved' && 
               !$this->posted_at;
    }

    public function approve(User $user): void
    {
        if (!$this->is_balanced) {
            throw new \Exception('Cannot approve unbalanced batch');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function post(User $user): void
    {
        if (!$this->canPost()) {
            throw new \Exception('Batch cannot be posted');
        }

        $this->update([
            'status' => 'posted',
            'posted_by' => $user->id,
            'posted_at' => now(),
        ]);
    }
}

