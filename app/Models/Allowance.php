<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function remunerations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Remuneration::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function cities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(City::class);
    }

    public function tripCategories(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(TripCategory::class, 'trip_categoryable');
    }
}
