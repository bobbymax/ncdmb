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
        Schema::create('project_lifecycle_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            $table->enum('stage_name', [
                'concept', 'feasibility', 'design', 'procurement', 
                'award', 'mobilization', 'execution', 'monitoring', 
                'completion', 'handover', 'closure', 'evaluation'
            ]);
            $table->integer('stage_order');
            
            // Stage Details
            $table->text('description')->nullable();
            $table->json('required_documents')->nullable();
            $table->json('required_approvals')->nullable();
            
            // Stage Status
            $table->enum('status', ['pending', 'in-progress', 'completed', 'skipped', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Responsibility
            $table->unsignedBigInteger('responsible_officer_id')->nullable();
            $table->unsignedBigInteger('responsible_department_id')->nullable();
            
            // Deliverables
            $table->text('expected_deliverables')->nullable();
            $table->text('actual_deliverables')->nullable();
            $table->boolean('deliverables_approved')->default(false);
            
            // Gate Approval
            $table->boolean('requires_gate_approval')->default(false);
            $table->enum('gate_approval_status', ['pending', 'approved', 'rejected', 'conditional'])->nullable();
            $table->date('gate_approval_date')->nullable();
            $table->unsignedBigInteger('gate_approver_id')->nullable();
            $table->text('gate_approval_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['project_id', 'stage_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_lifecycle_stages');
    }
};

