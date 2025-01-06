<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
//    public function workflowStages()
//    {
//        return $this->belongsToMany(WorkflowStage::class, 'workflow_workflow_stage')
//            ->withPivot('order')
//            ->withTimestamps();
//    }

    public function documentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function trackers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressTracker::class, 'workflow_id');
    }

    public function pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, 'workflow_id');
    }
}
