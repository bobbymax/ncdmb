<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Classification
            $table->enum('project_type', ['capital', 'operational', 'maintenance', 'research', 'infrastructure'])->default('capital')->after('project_category_id');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium')->after('project_type');
            $table->text('strategic_alignment')->nullable()->after('priority');

            // Organizational Links
            $table->unsignedBigInteger('ministry_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('implementing_agency_id')->nullable()->after('ministry_id');
            $table->unsignedBigInteger('sponsoring_department_id')->nullable()->after('implementing_agency_id');
            $table->unsignedBigInteger('project_manager_id')->nullable()->after('sponsoring_department_id');

            // Financial Information
            $table->foreignId('fund_id')->nullable()->after('threshold_id')->constrained('funds')->nullOnDelete();
            $table->string('budget_year', 20)->nullable()->after('fund_id');
            $table->string('budget_head_code', 50)->nullable()->after('budget_year');
            $table->decimal('total_revised_amount', 30, 2)->default(0)->after('total_approved_amount');
            $table->decimal('total_actual_cost', 30, 2)->default(0)->after('total_revised_amount');
            $table->decimal('contingency_amount', 30, 2)->default(0)->after('variation_amount');
            $table->integer('contingency_percentage')->default(10)->after('contingency_amount');

            // Lifecycle Stages
            $table->enum('lifecycle_stage', [
                'concept', 'feasibility', 'design', 'procurement',
                'award', 'mobilization', 'execution', 'monitoring',
                'completion', 'handover', 'closure', 'evaluation'
            ])->default('concept')->after('status');

            // Approval & Status (renaming existing status to approval_status)
            $table->enum('execution_status', ['not-started', 'in-progress', 'suspended', 'completed', 'terminated', 'cancelled'])->default('not-started')->after('lifecycle_stage');
            $table->enum('overall_health', ['on-track', 'at-risk', 'critical', 'completed'])->default('on-track')->after('execution_status');

            // Additional Dates
            $table->date('concept_date')->nullable()->after('proposed_start_date');
            $table->date('approval_date')->nullable()->after('concept_date');
            $table->date('commencement_order_date')->nullable()->after('approval_date');
            $table->date('revised_end_date')->nullable()->after('approved_end_date');
            $table->date('handover_date')->nullable()->after('actual_end_date');
            $table->date('warranty_expiry_date')->nullable()->after('handover_date');

            // Progress Metrics
            $table->decimal('physical_progress_percentage', 5, 2)->default(0)->after('warranty_expiry_date');
            $table->decimal('financial_progress_percentage', 5, 2)->default(0)->after('physical_progress_percentage');
            $table->decimal('time_elapsed_percentage', 5, 2)->default(0)->after('financial_progress_percentage');

            // Compliance & Governance
            $table->boolean('has_environmental_clearance')->default(false)->after('time_elapsed_percentage');
            $table->date('environmental_clearance_date')->nullable()->after('has_environmental_clearance');
            $table->boolean('has_land_acquisition')->default(false)->after('environmental_clearance_date');
            $table->enum('land_acquisition_status', ['not-required', 'in-progress', 'completed'])->default('not-required')->after('has_land_acquisition');
            $table->boolean('requires_public_consultation')->default(false)->after('land_acquisition_status');
            $table->boolean('public_consultation_completed')->default(false)->after('requires_public_consultation');

            // Risk & Issues
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low')->after('public_consultation_completed');
            $table->boolean('has_active_issues')->default(false)->after('risk_level');
            $table->integer('issues_count')->default(0)->after('has_active_issues');

            // Additional Metadata
            $table->boolean('is_multi_year')->default(false)->after('issues_count');
            $table->boolean('is_archived')->default(false)->after('is_multi_year');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_fund_id_foreign');
            $table->dropColumn([
                'project_type', 'priority', 'strategic_alignment',
                'ministry_id', 'implementing_agency_id', 'sponsoring_department_id', 'project_manager_id',
                'fund_id', 'budget_year', 'budget_head_code', 'total_revised_amount',
                'total_actual_cost', 'contingency_amount', 'contingency_percentage',
                'lifecycle_stage', 'execution_status', 'overall_health',
                'concept_date', 'approval_date', 'commencement_order_date', 'revised_end_date',
                'handover_date', 'warranty_expiry_date',
                'physical_progress_percentage', 'financial_progress_percentage', 'time_elapsed_percentage',
                'has_environmental_clearance', 'environmental_clearance_date', 'has_land_acquisition',
                'land_acquisition_status', 'requires_public_consultation', 'public_consultation_completed',
                'risk_level', 'has_active_issues', 'issues_count',
                'is_multi_year', 'is_archived', 'archived_at', 'archived_by',
                'deleted_at'
            ]);
        });
    }
};

