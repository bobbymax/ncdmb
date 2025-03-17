<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];
    protected $dates = ['deleted_at'];

    // Model Relationships or Scope Here...
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function authorisingOfficer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'authorising_staff_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function sponsor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'sponsoring_department_id');
    }

    public function expenditures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expenditure::class);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function touringAdvance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TouringAdvance::class);
    }

    public function trips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function document(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }
}
