<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    public function requirements(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(DocumentRequirement::class, 'document_typeable');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, 'workflow_id');
    }
}
