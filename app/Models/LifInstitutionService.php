<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifInstitutionService extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function lifActivities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LifActivity::class, 'lif_institution_service_id');
    }

    public function lifInstitution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LifInstitution::class, 'lif_institution_id');
    }

    public function lifService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LifService::class, 'lif_service_id');
    }

    public function contractor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class, 'contractor_id');
    }
}
