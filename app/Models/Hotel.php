<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function hotelReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HotelReservation::class);
    }

    public function gradeLevels(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(GradeLevel::class, 'hotelable');
    }

}
