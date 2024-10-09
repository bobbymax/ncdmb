<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function operator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class, 'operator_id');
    }

    public function scopes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectScope::class);
    }
}
