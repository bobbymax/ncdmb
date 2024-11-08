<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function brokers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Broker::class);
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Project::class, 'contractor_id');
    }

    public function boardProjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BoardProject::class, 'contractor_id');
    }

    public function eqApprovals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EQApproval::class, 'contractor_id');
    }

    public function lifInstitutionServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LifInstitutionService::class, 'contractor_id');
    }

    public function procuredMaterials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcuredMaterial::class, 'contractor_id');
    }

    public function scopes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectScope::class, 'contractor_id');
    }

    public function vendors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vendor::class, 'contractor_id');
    }

    public function vesselUtilizations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VesselUtilization::class);
    }
}
