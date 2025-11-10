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
        Schema::create('project_programs', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('code', 100)->unique();
            $table->string('title', 500);
            $table->text('description')->nullable();
            
            // Organizational Links
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('ministry_id')->nullable()->constrained('departments');
            $table->foreignId('project_category_id')->nullable()->constrained('project_categories');
            
            // Aggregate Financials (auto-calculated from phases)
            $table->decimal('total_estimated_amount', 30, 2)->default(0)->comment('Sum of all phase amounts');
            $table->decimal('total_approved_amount', 30, 2)->default(0)->comment('Sum of approved phase amounts');
            $table->decimal('total_actual_cost', 30, 2)->default(0)->comment('Sum of actual costs');
            
            // Timeline
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Classification
            $table->enum('status', ['concept', 'approved', 'active', 'suspended', 'completed', 'cancelled'])->default('concept');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->text('strategic_alignment')->nullable();
            
            // Progress (auto-calculated from phases)
            $table->decimal('overall_progress_percentage', 5, 2)->default(0);
            $table->enum('overall_health', ['on-track', 'at-risk', 'critical', 'completed'])->default('on-track');
            
            // Metadata
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('is_archived');
            $table->index('department_id');
            $table->index('ministry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_programs');
    }
};
