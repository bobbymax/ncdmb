<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalAuditTrail extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'performed_at' => 'datetime',
        'before_values' => 'array',
        'after_values' => 'array',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function projectContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectContract::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function performedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
