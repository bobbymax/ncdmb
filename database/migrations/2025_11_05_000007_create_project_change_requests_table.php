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
        Schema::create('project_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Change Request Details
            $table->string('change_request_code', 50)->unique();
            $table->string('change_title', 500);
            $table->text('change_description')->nullable();
            $table->enum('change_category', ['scope', 'schedule', 'budget', 'quality', 'resource', 'design']);
            $table->text('change_reason')->nullable();
            
            // Impact Assessment
            $table->decimal('cost_impact', 30, 2)->default(0);
            $table->integer('schedule_impact_days')->default(0);
            $table->text('quality_impact')->nullable();
            $table->text('risk_impact')->nullable();
            
            // Approval Process
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->date('requested_date')->nullable();
            $table->enum('approval_status', ['draft', 'submitted', 'under-review', 'approved', 'rejected'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Implementation
            $table->enum('implementation_status', ['not-started', 'in-progress', 'completed'])->default('not-started');
            $table->date('implemented_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_change_requests');
    }
};

