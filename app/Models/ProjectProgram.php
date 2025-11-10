<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'archived_at' => 'datetime',
        'is_archived' => 'boolean',
        'overall_progress_percentage' => 'decimal:2',
        'total_estimated_amount' => 'decimal:2',
        'total_approved_amount' => 'decimal:2',
        'total_actual_cost' => 'decimal:2',
    ];

    protected $appends = ['total_phases', 'active_phases', 'completed_phases'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function ministry()
    {
        return $this->belongsTo(Department::class, 'ministry_id');
    }

    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    // The phases (projects that belong to this program)
    public function phases()
    {
        return $this->hasMany(Project::class, 'program_id')
                    ->orderBy('phase_order');
    }

    // Alias for clarity
    public function projects()
    {
        return $this->phases();
    }

    // Computed attributes
    public function getTotalPhasesAttribute()
    {
        return $this->phases()->count();
    }

    public function getActivePhasesAttribute()
    {
        return $this->phases()
                    ->whereIn('execution_status', ['in-progress'])
                    ->count();
    }

    public function getCompletedPhasesAttribute()
    {
        return $this->phases()
                    ->where('execution_status', 'completed')
                    ->count();
    }

    // Aggregate calculations
    public function recalculateFinancials()
    {
        $this->total_estimated_amount = $this->phases()->sum('total_proposed_amount');
        $this->total_approved_amount = $this->phases()->sum('total_approved_amount');
        $this->total_actual_cost = $this->phases()->sum('total_actual_cost');
        $this->save();
    }

    public function recalculateProgress()
    {
        $phases = $this->phases()->get();
        
        if ($phases->isEmpty()) {
            $this->overall_progress_percentage = 0;
            $this->overall_health = 'on-track';
        } else {
            // Average of phase progress
            $this->overall_progress_percentage = $phases->avg('physical_progress_percentage') ?? 0;
            
            // Determine overall health (worst case)
            $healthPriority = ['critical' => 4, 'at-risk' => 3, 'on-track' => 2, 'completed' => 1];
            $worstHealth = $phases->map(fn($p) => $healthPriority[$p->overall_health] ?? 2)->max();
            $this->overall_health = array_search($worstHealth, $healthPriority);
        }
        
        $this->save();
    }

    // Boot method to generate code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->code)) {
                $program->code = self::generateCode();
            }
        });
    }

    private static function generateCode()
    {
        $year = date('Y');
        $lastProgram = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();
        
        $number = $lastProgram ? (int)substr($lastProgram->code, -3) + 1 : 1;
        
        return sprintf('PROG-%s-%03d', $year, $number);
    }
}
