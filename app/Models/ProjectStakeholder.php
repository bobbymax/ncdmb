<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStakeholder extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('stakeholder_type', $type);
    }

    public function scopeHighInfluence($query)
    {
        return $query->where('influence_level', 'high');
    }

    public function scopeHighEngagement($query)
    {
        return $query->where('engagement_level', 'high');
    }

    public function scopeDecisionMakers($query)
    {
        return $query->where('authority_level', 'decision-maker');
    }
}

