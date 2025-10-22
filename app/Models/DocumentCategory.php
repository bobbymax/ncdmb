<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'config' => 'json',
        'meta_data' => 'json',
        'workflow' => 'json',
        'content' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function documentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function template(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Template::class);
    }

    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function stages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowStage::class, 'document_category_id');
    }

    public function requirements(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentRequirement::class, 'document_requirementable');
    }

    public function blocks(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Block::class, 'blockable');
    }

    public function signatories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Signatory::class, 'document_category_id');
    }
}
