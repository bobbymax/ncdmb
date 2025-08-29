<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function milestones(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Milestone::class, 'milestoneable');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }
}
