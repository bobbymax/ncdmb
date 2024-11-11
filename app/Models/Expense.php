<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...

    public function claim(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function remuneration(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Remuneration::class);
    }
}
