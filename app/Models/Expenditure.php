<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expenditure extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];
    protected array $dates = ['deleted_at'];

    // Model Relationships or Scope Here...

    public function controller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function fund(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function expenditureable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function draft(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(DocumentDraft::class, 'document_draftable');
    }

    public function document(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_reference_id');
    }
}
