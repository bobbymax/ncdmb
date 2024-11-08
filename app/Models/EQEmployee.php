<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EQEmployee extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function eqApproval(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EQApproval::class);
    }

    public function expatriates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EQSuccessionPlan::class, 'expatriate_id');
    }

    public function understudies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EQSuccessionPlan::class, 'understudy_id');
    }
}
