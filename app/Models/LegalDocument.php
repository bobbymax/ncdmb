<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'signed_at' => 'datetime',
        'is_current' => 'boolean',
        'requires_signature' => 'boolean',
        'signed_by' => 'array',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function projectContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectContract::class);
    }

    public function uploadedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
