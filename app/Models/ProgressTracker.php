<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressTracker extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function actions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentAction::class, 'document_actionable');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(MailingList::class, 'mailing_listable');
    }
}
