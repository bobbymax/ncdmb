<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectBid extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'bid_documents' => 'array',
        'bid_security_submitted' => 'boolean',
        'is_administratively_compliant' => 'boolean',
        'is_financially_responsive' => 'boolean',
        'submitted_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bidInvitation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectBidInvitation::class, 'bid_invitation_id');
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function receivedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function openedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function evaluations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectBidEvaluation::class, 'project_bid_id');
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'opened', 'responsive', 'under_evaluation', 'evaluated']);
    }

    public function scopeResponsive($query)
    {
        return $query->where('status', 'responsive');
    }

    public function scopeRecommended($query)
    {
        return $query->where('status', 'recommended');
    }

    public function scopeAwarded($query)
    {
        return $query->where('status', 'awarded');
    }
}
