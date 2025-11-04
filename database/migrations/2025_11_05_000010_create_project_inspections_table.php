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
        Schema::create('project_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('milestone_id')->nullable();
            $table->foreign('milestone_id')->references('id')->on('project_milestones')->onDelete('set null');
            
            // Inspection Details
            $table->string('inspection_code', 100)->unique();
            $table->enum('inspection_type', ['site-inspection', 'quality-control', 'safety-audit', 'progress-verification', 'final-inspection']);
            $table->date('inspection_date');
            
            // Inspection Team
            $table->unsignedBigInteger('lead_inspector_id')->nullable();
            $table->json('inspection_team')->nullable()->comment('Array of user IDs');
            
            // Findings
            $table->enum('overall_rating', ['excellent', 'satisfactory', 'needs-improvement', 'unsatisfactory', 'critical'])->nullable();
            $table->text('findings')->nullable();
            $table->json('deficiencies')->nullable()->comment('List of deficiencies found');
            $table->text('recommendations')->nullable();
            
            // Follow-up
            $table->boolean('requires_followup')->default(false);
            $table->date('followup_date')->nullable();
            $table->text('corrective_actions')->nullable();
            
            // Status
            $table->enum('status', ['scheduled', 'in-progress', 'completed', 'cancelled'])->default('scheduled');
            
            // Documents
            $table->string('report_file_path', 500)->nullable();
            $table->json('photos')->nullable()->comment('Array of photo URLs');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_inspections');
    }
};

