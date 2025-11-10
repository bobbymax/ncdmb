<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBidEvaluation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'criteria' => 'array',
        'evaluation_date' => 'date',
    ];

    // Relationships
    public function projectBid(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectBid::class, 'project_bid_id');
    }

    public function evaluator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Scopes
    public function scopeTechnical($query)
    {
        return $query->where('evaluation_type', 'technical');
    }

    public function scopeFinancial($query)
    {
        return $query->where('evaluation_type', 'financial');
    }

    public function scopeAdministrative($query)
    {
        return $query->where('evaluation_type', 'administrative');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'reviewed', 'approved']);
    }
}
