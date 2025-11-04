<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLifecycleStage extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'required_documents' => 'array',
        'required_approvals' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'gate_approval_date' => 'date',
        'deliverables_approved' => 'boolean',
        'requires_gate_approval' => 'boolean',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function responsibleOfficer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_officer_id');
    }

    public function responsibleDepartment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'responsible_department_id');
    }

    public function gateApprover(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'gate_approver_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('stage_order');
    }

    // Computed Attributes
    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        
        return $this->started_at->diffInDays($this->completed_at);
    }
}

