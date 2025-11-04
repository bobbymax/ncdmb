<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectChangeRequest extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'requested_date' => 'date',
        'approved_date' => 'date',
        'implemented_date' => 'date',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('approval_status', ['draft', 'submitted', 'under-review']);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    public function scopeImplemented($query)
    {
        return $query->where('implementation_status', 'completed');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('change_category', $category);
    }

    // Computed Attributes
    public function getIsImplementedAttribute(): bool
    {
        return $this->implementation_status === 'completed';
    }

    public function getTotalImpactAttribute(): string
    {
        $impacts = [];
        
        if ($this->cost_impact != 0) {
            $impacts[] = "Cost: " . number_format($this->cost_impact, 2);
        }
        
        if ($this->schedule_impact_days != 0) {
            $impacts[] = "Schedule: {$this->schedule_impact_days} days";
        }
        
        return implode(', ', $impacts) ?: 'No impact';
    }
}

