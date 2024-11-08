<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EQSuccessionPlan extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function expatriate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EQEmployee::class, 'expatriate_id');
    }

    public function understudy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EQEmployee::class, 'understudy_id');
    }
}
