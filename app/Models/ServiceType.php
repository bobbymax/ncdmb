<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function renderedServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RenderedService::class);
    }
}
