<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RNDProject extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function accomodations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchAccomodation::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function centres(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchCentre::class);
    }

    public function disseminations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchDissemination::class);
    }

    public function libraries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchLibrary::class);
    }

    public function outcomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchOutcome::class);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchBudget::class);
    }

    public function facilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchFacility::class);
    }

    public function investigators(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PrincipalInvestigator::class, 'r_n_d_project_id');
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchTeam::class);
    }

    public function teamDevelopments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResearchTeamDevelopment::class);
    }
}
