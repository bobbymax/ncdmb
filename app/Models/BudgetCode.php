<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCode extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function funds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fund::class);
    }
}
