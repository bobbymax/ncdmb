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
        Schema::create('project_feasibility_studies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Study Information
            $table->enum('study_type', ['technical', 'financial', 'economic', 'social', 'environmental']);
            $table->string('conducted_by')->nullable();
            $table->date('conducted_date')->nullable();
            
            // Findings
            $table->boolean('is_feasible')->nullable();
            $table->decimal('feasibility_score', 5, 2)->nullable()->comment('0-100');
            $table->text('key_findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('constraints_identified')->nullable();
            
            // Economic Analysis
            $table->decimal('benefit_cost_ratio', 10, 4)->nullable();
            $table->decimal('net_present_value', 30, 2)->nullable();
            $table->decimal('internal_rate_of_return', 5, 2)->nullable();
            $table->integer('payback_period_months')->nullable();
            
            // Documents
            $table->string('report_file_path', 500)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_feasibility_studies');
    }
};

