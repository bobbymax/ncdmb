<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubBudgetHead extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function budgetHead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BudgetHead::class);
    }

    public function funds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fund::class);
    }

    public function budgetPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BudgetPlan::class);
    }
}
