<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function signatory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'signatory_id');
    }

    public function documentDraft(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentDraft::class, 'document_draft_id');
    }
}
