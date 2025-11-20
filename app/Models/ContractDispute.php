<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDispute extends Model
{
    use HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'raised_date' => 'date',
        'resolved_date' => 'date',
        'disputed_amount' => 'decimal:2',
        'resolved_amount' => 'decimal:2',
        'supporting_documents' => 'array',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function projectContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectContract::class);
    }

    public function legalCounsel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'legal_counsel_id');
    }
}
