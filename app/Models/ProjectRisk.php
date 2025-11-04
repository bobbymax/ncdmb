<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRisk extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'identified_date' => 'date',
        'last_reviewed_date' => 'date',
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function riskOwner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'risk_owner_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed', 'occurred']);
    }

    public function scopeCritical($query)
    {
        return $query->where('risk_level', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('risk_level', 'high');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('risk_category', $category);
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_closure_date', '<', now())
            ->whereNull('actual_closure_date')
            ->whereNotIn('status', ['closed', 'occurred']);
    }

    // Computed Attributes
    public function getIsOverdueAttribute(): bool
    {
        return $this->target_closure_date && 
               now()->gt($this->target_closure_date) &&
               !$this->actual_closure_date &&
               !in_array($this->status, ['closed', 'occurred']);
    }
}

