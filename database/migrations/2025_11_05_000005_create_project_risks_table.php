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
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Risk Identification
            $table->string('risk_code', 50)->unique();
            $table->string('risk_title', 500);
            $table->text('risk_description')->nullable();
            $table->enum('risk_category', [
                'financial', 'technical', 'schedule', 'quality',
                'safety', 'environmental', 'legal', 'political', 
                'procurement', 'operational'
            ]);
            
            // Risk Assessment
            $table->enum('likelihood', ['very-low', 'low', 'medium', 'high', 'very-high'])->default('medium');
            $table->enum('impact', ['negligible', 'minor', 'moderate', 'major', 'critical'])->default('moderate');
            $table->decimal('risk_score', 5, 2)->nullable()->comment('Calculated: likelihood × impact');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Risk Response
            $table->enum('response_strategy', ['avoid', 'mitigate', 'transfer', 'accept'])->default('mitigate');
            $table->text('mitigation_plan')->nullable();
            $table->text('contingency_plan')->nullable();
            
            // Ownership & Status
            $table->unsignedBigInteger('risk_owner_id')->nullable();
            $table->enum('status', ['identified', 'analyzing', 'planning', 'mitigating', 'closed', 'occurred'])->default('identified');
            
            // Monitoring
            $table->date('identified_date')->nullable();
            $table->date('last_reviewed_date')->nullable();
            $table->date('target_closure_date')->nullable();
            $table->date('actual_closure_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};

