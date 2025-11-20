<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalClearance extends Model
{
    use HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'clearance_date' => 'date',
        'expiry_date' => 'date',
        'conditions' => 'array',
        'compliance_requirements' => 'array',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function projectContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectContract::class);
    }

    public function clearedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
