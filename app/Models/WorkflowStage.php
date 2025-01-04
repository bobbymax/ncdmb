<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function workflowStageCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowStageCategory::class);
    }
    public function actions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentAction::class, 'document_actionable');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function supportGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'assistant_group_id');
    }

    public function requirements(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentRequirement::class, 'document_requirementable');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    // Define the relationship with Workflow
    public function workflows()
    {
        return $this->belongsToMany(Workflow::class, 'workflow_workflow_stage')
            ->withPivot('order') // Include 'order' column in the relationship
            ->withTimestamps(); // Track timestamps
    }
}
