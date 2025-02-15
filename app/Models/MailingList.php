<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailingList extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function trackers(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(ProgressTracker::class , 'mailing_listable');
    }

    public function workflowStages(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(WorkflowStage::class, 'mailing_listable');
    }
}
