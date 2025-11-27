<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function handler(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function representatives(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompanyRepresentative::class, 'company_id');
    }
}
