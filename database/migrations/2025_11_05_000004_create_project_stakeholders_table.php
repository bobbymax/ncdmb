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
        Schema::create('project_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Stakeholder Information
            $table->enum('stakeholder_type', [
                'sponsor', 'project-manager', 'team-member', 
                'contractor', 'consultant', 'beneficiary', 
                'regulatory-body', 'community', 'donor'
            ]);
            $table->string('stakeholder_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            
            // Role & Responsibility
            $table->text('role_description')->nullable();
            $table->text('responsibility')->nullable();
            $table->enum('authority_level', ['decision-maker', 'approver', 'contributor', 'informed'])->nullable();
            
            // Engagement
            $table->enum('engagement_level', ['high', 'medium', 'low'])->default('medium');
            $table->enum('influence_level', ['high', 'medium', 'low'])->default('medium');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stakeholders');
    }
};

