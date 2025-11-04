<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPerformanceMetric extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'reporting_period' => 'date',
    ];

    public $timestamps = true;

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeLatest($query)
    {
        return $query->orderByDesc('reporting_period');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('reporting_period', [$startDate, $endDate]);
    }

    // Computed Attributes
    public function getIsOnTrackAttribute(): bool
    {
        return $this->schedule_performance_index >= 0.95 && 
               $this->cost_performance_index >= 0.95;
    }

    public function getIsAtRiskAttribute(): bool
    {
        return ($this->schedule_performance_index < 0.95 && $this->schedule_performance_index >= 0.80) ||
               ($this->cost_performance_index < 0.95 && $this->cost_performance_index >= 0.80);
    }

    public function getIsCriticalAttribute(): bool
    {
        return $this->schedule_performance_index < 0.80 || 
               $this->cost_performance_index < 0.80;
    }

    public function getPerformanceStatusAttribute(): string
    {
        if ($this->getIsOnTrackAttribute()) {
            return 'on-track';
        } elseif ($this->getIsAtRiskAttribute()) {
            return 'at-risk';
        } else {
            return 'critical';
        }
    }
}

