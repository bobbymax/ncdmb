<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function remunerations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Remuneration::class);
    }

    public function hotels(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Hotel::class, 'hotelable');
    }
}
