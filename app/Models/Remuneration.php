<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remuneration extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function allowance(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Allowance::class, 'allowance_id');
    }

    public function gradeLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }
}
