<?php

namespace App\Models;

use App\Traits\NotifiesOnChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundInstruction extends Model
{
    use HasFactory, NotifiesOnChanges;

    protected $guarded = [''];
    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'notes' => 'json'
    ];

    // Model Relationships or Scope Here...
    public function inbound(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Inbound::class, 'inbound_id');
    }

    public function champion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function attendee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function assignable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
