<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Project::class, 'operator_id');
    }

    public function scopes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectScope::class, 'contractor_id');
    }
}
