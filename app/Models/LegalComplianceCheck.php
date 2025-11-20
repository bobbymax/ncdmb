<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalComplianceCheck extends Model
{
    use HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'check_date' => 'date',
        'follow_up_date' => 'date',
        'compliance_score' => 'decimal:2',
        'requires_remediation' => 'boolean',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function projectContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectContract::class);
    }

    public function checkedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
