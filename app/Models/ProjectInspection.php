<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInspection extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'inspection_date' => 'date',
        'followup_date' => 'date',
        'requires_followup' => 'boolean',
        'inspection_team' => 'array',
        'deficiencies' => 'array',
        'photos' => 'array',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function leadInspector(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_inspector_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('inspection_type', $type);
    }

    public function scopeRequiringFollowup($query)
    {
        return $query->where('requires_followup', true)
            ->whereNull('followup_date');
    }

    public function scopeUnsatisfactory($query)
    {
        return $query->whereIn('overall_rating', ['needs-improvement', 'unsatisfactory', 'critical']);
    }

    // Computed Attributes
    public function getHasDeficienciesAttribute(): bool
    {
        return !empty($this->deficiencies);
    }

    public function getDeficienciesCountAttribute(): int
    {
        return is_array($this->deficiencies) ? count($this->deficiencies) : 0;
    }
}

