<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFeasibilityStudy extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'conducted_date' => 'date',
        'approved_at' => 'datetime',
        'is_feasible' => 'boolean',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeFeasible($query)
    {
        return $query->where('is_feasible', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('study_type', $type);
    }

    // Computed Attributes
    public function getIsApprovedAttribute(): bool
    {
        return !is_null($this->approved_at);
    }
}

