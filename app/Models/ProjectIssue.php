<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectIssue extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'reported_date' => 'date',
        'target_resolution_date' => 'date',
        'actual_resolution_date' => 'date',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function raisedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function assignedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function escalatedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'investigating', 'in-progress']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_resolution_date', '<', now())
            ->whereNull('actual_resolution_date')
            ->whereIn('status', ['open', 'investigating', 'in-progress']);
    }

    // Computed Attributes
    public function getIsOverdueAttribute(): bool
    {
        return $this->target_resolution_date && 
               now()->gt($this->target_resolution_date) &&
               !$this->actual_resolution_date &&
               in_array($this->status, ['open', 'investigating', 'in-progress']);
    }

    public function getResolutionTimeDaysAttribute(): ?int
    {
        if (!$this->reported_date || !$this->actual_resolution_date) {
            return null;
        }
        
        return $this->reported_date->diffInDays($this->actual_resolution_date);
    }
}

