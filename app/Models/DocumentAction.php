<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAction extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class, 'document_action_id');
    }

    public function trackers(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(ProgressTracker::class , 'document_actionable');
    }

    public function updates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentUpdate::class);
    }

    public function carder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carder::class, 'carder_id');
    }

    public function workflowStages(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(WorkflowStage::class, 'document_actionable');
    }
}
