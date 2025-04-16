<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentDraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];
    protected array $dates = ['deleted_at'];

    protected $casts = [
        'signature' => 'string',
    ];

    // Model Relationships or Scope Here...
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentComment::class, 'document_draft_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function updates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentUpdate::class);
    }

    public function document(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function linkedDocument(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Document::class, 'sub_document_reference_id');
    }

    public function documentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function workflowStage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'current_workflow_stage_id');
    }

    public function documentAction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentAction::class, 'document_action_id');
    }

    public function authorisingStaff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'authorising_staff_id');
    }

    public function documentDraftable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function approval(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Signature::class, 'document_draft_id');
    }

    public function tracker(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProgressTracker::class , 'progress_tracker_id');
    }

    public function upload(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Upload::class, 'uploadable');
    }
}
