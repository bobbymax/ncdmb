<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectContract extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'performance_bond_required' => 'boolean',
        'performance_bond_submitted' => 'boolean',
        'advance_payment_allowed' => 'boolean',
        'contract_signed' => 'boolean',
        'award_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'tenders_board_approval_date' => 'date',
        'published_at' => 'datetime',
        'contract_signed_date' => 'date',
        'standstill_start_date' => 'date',
        'standstill_end_date' => 'date',
        'date_of_acceptance' => 'date',
        'is_completed' => 'boolean',
    ];

    // Model Relationships or Scope Here...
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function boardProject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BoardProject::class, 'board_project_id');
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function supplies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreSupply::class);
    }
}
