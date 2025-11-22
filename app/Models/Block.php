<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $casts = [
        'schema' => 'json',
    ];

    // Model Relationships or Scope Here...

    public function documentCategories(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(DocumentCategory::class, 'blockable');
    }

    public function templates(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Template::class, 'blockable');
    }
}
