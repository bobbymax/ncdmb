<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEvaluationCommittee extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'members' => 'array',
        'formed_at' => 'datetime',
        'dissolved_at' => 'datetime',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function chairman(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTenderBoard($query)
    {
        return $query->where('committee_type', 'tender_board');
    }

    public function scopeTechnical($query)
    {
        return $query->where('committee_type', 'technical');
    }

    public function scopeFinancial($query)
    {
        return $query->where('committee_type', 'financial');
    }
}
