<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureRequest extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function authorisingOfficer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function document(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function draft(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentDraft::class, 'document_draft_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
