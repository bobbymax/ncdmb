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
        Schema::create('project_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->date('reporting_period');
            
            // Earned Value Metrics
            $table->decimal('planned_value', 30, 2)->default(0);
            $table->decimal('earned_value', 30, 2)->default(0);
            $table->decimal('actual_cost', 30, 2)->default(0);
            
            // Performance Indices
            $table->decimal('schedule_performance_index', 10, 4)->nullable()->comment('EV/PV');
            $table->decimal('cost_performance_index', 10, 4)->nullable()->comment('EV/AC');
            
            // Variance Analysis
            $table->decimal('schedule_variance', 30, 2)->nullable()->comment('EV - PV');
            $table->decimal('cost_variance', 30, 2)->nullable()->comment('EV - AC');
            
            // Forecasting
            $table->decimal('estimate_at_completion', 30, 2)->nullable();
            $table->decimal('estimate_to_complete', 30, 2)->nullable();
            $table->decimal('variance_at_completion', 30, 2)->nullable();
            $table->decimal('to_complete_performance_index', 10, 4)->nullable();
            
            // Quality Metrics
            $table->decimal('quality_score', 5, 2)->nullable()->comment('0-100');
            $table->integer('defects_count')->default(0);
            $table->decimal('rework_percentage', 5, 2)->nullable();
            
            // Safety Metrics
            $table->integer('safety_incidents_count')->default(0);
            $table->integer('lost_time_injuries')->default(0);
            
            // Progress Metrics
            $table->integer('activities_planned')->default(0);
            $table->integer('activities_completed')->default(0);
            $table->integer('milestones_achieved')->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'reporting_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_performance_metrics');
    }
};

