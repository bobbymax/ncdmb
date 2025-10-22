<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressTracker extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected static function booted(): void
    {
        static::deleting(function ($model) {
            $model->actions()->detach();
            $model->recipients()->detach();
        });
    }

    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function processCard(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProcessCard::class, 'process_card_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function internalProcess(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'internal_process_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function carder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carder::class, 'carder_id');
    }

    public function documentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function actions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentAction::class, 'document_actionable');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(MailingList::class, 'mailing_listable');
    }

    public function widgets(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(
            Widget::class,
            'trackable',
            'trackables',
            'progress_tracker_id',
            'trackable_id'
        );
    }

    public function signatory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'signatory_id');
    }
}
