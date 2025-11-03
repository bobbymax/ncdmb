<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inbound extends Model
{
    use SoftDeletes, HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'published_at' => 'datetime',
        'received_at' => 'datetime',
        'mailed' => 'datetime',
        'analysis' => 'json',
    ];

    // Model Relationships or Scope Here...

    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function authorisingOfficer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'authorising_officer_id');
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function instructions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InboundInstruction::class, 'inbound_id');
    }
}
