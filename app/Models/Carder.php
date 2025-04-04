<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carder extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function gradeLevels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function actions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentAction::class);
    }

    public function trackers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressTracker::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }
}
