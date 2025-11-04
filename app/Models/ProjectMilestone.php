<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'approval_date' => 'date',
        'is_paid' => 'boolean',
        'is_critical_path' => 'boolean',
        'dependencies' => 'array',
        'deliverables' => 'array',
    ];

    // Existing Relationships
    public function boardProject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BoardProject::class);
    }

    public function expenditures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expenditure::class);
    }

    public function mandates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Mandate::class);
    }

    // New Relationships
    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function inspections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectInspection::class, 'milestone_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeCriticalPath($query)
    {
        return $query->where('is_critical_path', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('milestone_type', $type);
    }

    // Computed Attributes
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue' || 
               ($this->expected_completion_date && 
                now()->gt($this->expected_completion_date) && 
                !$this->actual_completion_date);
    }

    public function getCompletionPercentageAttribute(): float
    {
        return (float) $this->percentage_project_completion;
    }
}
