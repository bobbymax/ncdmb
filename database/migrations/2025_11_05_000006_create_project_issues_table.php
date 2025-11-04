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
        Schema::create('project_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Issue Identification
            $table->string('issue_code', 50)->unique();
            $table->string('issue_title', 500);
            $table->text('issue_description')->nullable();
            $table->enum('issue_type', ['technical', 'schedule', 'cost', 'quality', 'scope', 'resource', 'external']);
            
            // Severity & Priority
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Status & Resolution
            $table->enum('status', ['open', 'investigating', 'in-progress', 'resolved', 'closed', 'escalated'])->default('open');
            $table->text('resolution_description')->nullable();
            
            // Ownership
            $table->unsignedBigInteger('raised_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('escalated_to')->nullable();
            
            // Dates
            $table->date('reported_date')->nullable();
            $table->date('target_resolution_date')->nullable();
            $table->date('actual_resolution_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_issues');
    }
};

