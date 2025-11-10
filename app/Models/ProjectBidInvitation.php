<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBidInvitation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'required_documents' => 'array',
        'eligibility_criteria' => 'array',
        'evaluation_criteria' => 'array',
        'published_newspapers' => 'array',
        'bid_security_required' => 'boolean',
        'published_bpp_portal' => 'boolean',
        'advertisement_date' => 'date',
        'pre_bid_meeting_date' => 'datetime',
        'submission_deadline' => 'datetime',
        'opening_date' => 'datetime',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bids(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectBid::class, 'bid_invitation_id');
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'published')
            ->where('submission_deadline', '>', now());
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed')
            ->orWhere(function($q) {
                $q->where('status', 'published')
                  ->where('submission_deadline', '<=', now());
            });
    }
}
