<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTemplate extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function documentTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentType::class);
    }
}
