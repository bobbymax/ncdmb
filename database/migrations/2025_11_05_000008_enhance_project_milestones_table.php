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
        Schema::table('project_milestones', function (Blueprint $table) {
            // Milestone Type
            $table->enum('milestone_type', ['planning', 'design', 'procurement', 'construction', 'testing', 'commissioning', 'handover'])->default('construction')->after('name');
            $table->boolean('is_critical_path')->default(false)->after('milestone_type');
            
            // Dependencies and Deliverables
            $table->json('dependencies')->nullable()->after('is_critical_path')->comment('List of milestone IDs this depends on');
            $table->json('deliverables')->nullable()->after('dependencies')->comment('Expected deliverables');
            $table->text('acceptance_criteria')->nullable()->after('deliverables');
            
            // Approval
            $table->unsignedBigInteger('approved_by')->nullable()->after('acceptance_criteria');
            $table->date('approval_date')->nullable()->after('approved_by');
            
            // Variance Tracking
            $table->integer('variance_days')->default(0)->after('actual_completion_date')->comment('Difference between planned and actual');
            $table->text('variance_reason')->nullable()->after('variance_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn([
                'milestone_type', 'is_critical_path', 'dependencies', 'deliverables', 
                'acceptance_criteria', 'approved_by', 'approval_date', 
                'variance_days', 'variance_reason'
            ]);
        });
    }
};

