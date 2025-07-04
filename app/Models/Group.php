<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function workflowStages(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(WorkflowStage::class, 'groupable');
    }

    public function carders(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Carder::class, 'groupable');
    }

    public function documentDrafts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentDraft::class);
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MailingList::class);
    }

    public function signatories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Signatory::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'groupable');
    }

    public function trackers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressTracker::class);
    }

    public function widgets(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Widget::class, 'groupable');
    }

    public function ledgers(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Ledger::class, 'groupable');
    }

    public function editors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResourceEditor::class);
    }
}
