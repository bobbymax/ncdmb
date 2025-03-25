<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public static $type = ['owner', 'initiator', 'vendor', 'witness', 'approval', 'authorised', 'attestation', 'auditor', 'other'];

    // Model Relationships or Scope Here...
    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function signatures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Signature::class, 'signatory_id');
    }

    public function stageTrackers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressTracker::class , 'signatory_id');
    }
}
