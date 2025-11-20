<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'concept_date' => 'date',
        'approval_date' => 'date',
        'commencement_order_date' => 'date',
        'proposed_start_date' => 'date',
        'approved_start_date' => 'date',
        'actual_start_date' => 'date',
        'proposed_end_date' => 'date',
        'approved_end_date' => 'date',
        'revised_end_date' => 'date',
        'actual_end_date' => 'date',
        'handover_date' => 'date',
        'warranty_expiry_date' => 'date',
        'environmental_clearance_date' => 'date',
        'archived_at' => 'datetime',
        'has_environmental_clearance' => 'boolean',
        'has_land_acquisition' => 'boolean',
        'requires_public_consultation' => 'boolean',
        'public_consultation_completed' => 'boolean',
        'has_active_issues' => 'boolean',
        'is_multi_year' => 'boolean',
        'is_archived' => 'boolean',
        'requires_bpp_clearance' => 'boolean',
        'bpp_invite_date' => 'date',
        'bpp_award_date' => 'date',
        'advertised_at' => 'datetime',
    ];

    // Organizational Relationships
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ministry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'ministry_id');
    }

    public function implementingAgency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'implementing_agency_id');
    }

    public function sponsoringDepartment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'sponsoring_department_id');
    }

    public function projectManager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function archivedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    // Classification Relationships
    public function threshold(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Threshold::class);
    }

    public function projectCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    public function fund(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    // Existing Polymorphic Relationships
    public function milestones(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Milestone::class, 'milestoneable');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    // New Lifecycle Management Relationships
    public function lifecycleStages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectLifecycleStage::class);
    }

    public function currentLifecycleStage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectLifecycleStage::class)
            ->where('status', 'in-progress')
            ->orWhere('status', 'pending')
            ->orderBy('stage_order');
    }

    public function feasibilityStudies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectFeasibilityStudy::class);
    }

    public function stakeholders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectStakeholder::class);
    }

    public function activeStakeholders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectStakeholder::class)->where('is_active', true);
    }

    public function risks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectRisk::class);
    }

    public function activeRisks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectRisk::class)
            ->whereNotIn('status', ['closed', 'occurred']);
    }

    public function criticalRisks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectRisk::class)
            ->where('risk_level', 'critical')
            ->whereNotIn('status', ['closed', 'occurred']);
    }

    public function issues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectIssue::class);
    }

    public function openIssues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectIssue::class)
            ->whereIn('status', ['open', 'investigating', 'in-progress']);
    }

    public function changeRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectChangeRequest::class);
    }

    public function pendingChangeRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectChangeRequest::class)
            ->whereIn('approval_status', ['draft', 'submitted', 'under-review']);
    }

    public function performanceMetrics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectPerformanceMetric::class);
    }

    public function latestPerformanceMetric(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectPerformanceMetric::class)
            ->latestOfMany('reporting_period');
    }

    public function inspections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectInspection::class);
    }

    public function completedInspections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectInspection::class)
            ->where('status', 'completed');
    }

    // Procurement Relationships
    public function bidInvitation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectBidInvitation::class);
    }

    public function bids(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectBid::class);
    }

    public function evaluationCommittees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectEvaluationCommittee::class);
    }

    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectContract::class);
    }

    public function procurementAuditTrails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcurementAuditTrail::class);
    }

    // Legal Cycle Relationships
    public function legalReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LegalReview::class);
    }

    public function legalAuditTrails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LegalAuditTrail::class);
    }

    // Program/Phase Relationship
    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectProgram::class, 'program_id');
    }

    // Helper methods for program/phase management
    public function isPhase(): bool
    {
        return !is_null($this->program_id);
    }

    public function isStandalone(): bool
    {
        return is_null($this->program_id);
    }

    public function getProcurementThresholdAmount(): float
    {
        // For phases, use the phase's amount
        // For standalone projects, use the project's amount
        return $this->total_proposed_amount;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeByLifecycleStage($query, $stage)
    {
        return $query->where('lifecycle_stage', $stage);
    }

    public function scopeByHealth($query, $health)
    {
        return $query->where('overall_health', $health);
    }

    public function scopeAtRisk($query)
    {
        return $query->whereIn('overall_health', ['at-risk', 'critical']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Computed Attributes
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->approved_end_date) {
            return false;
        }

        return now()->gt($this->approved_end_date) &&
               $this->execution_status === 'in-progress';
    }

    public function getTimeRemainingDaysAttribute(): ?int
    {
        if (!$this->approved_end_date) {
            return null;
        }

        return now()->diffInDays($this->approved_end_date, false);
    }

    public function getTotalVarianceAttribute(): float
    {
        return $this->total_actual_cost - $this->total_approved_amount;
    }

    // Boot method for program recalculation
    protected static function boot()
    {
        parent::boot();

        // Recalculate program financials when phase is updated
        static::saved(function ($project) {
            if ($project->program_id) {
                $project->program->recalculateFinancials();
                $project->program->recalculateProgress();
            }
        });

        static::deleted(function ($project) {
            if ($project->program_id && $project->program) {
                $project->program->recalculateFinancials();
                $project->program->recalculateProgress();
            }
        });
    }
}
