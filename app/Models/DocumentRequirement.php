<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function documentTypes(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(DocumentType::class, 'document_typeable');
    }

    public function workflowStages(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(WorkflowStage::class, 'document_requirementable');
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(DocumentCategory::class, 'document_requirementable');
    }
}
