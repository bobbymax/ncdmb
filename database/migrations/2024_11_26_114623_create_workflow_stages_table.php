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
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - properly defined
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('workflow_stage_category_id')->nullable();
            $table->unsignedBigInteger('fallback_stage_id')->nullable();
            
            // Core fields
            $table->string('name');
            
            // Status and flags
            $table->boolean('can_appeal')->default(false);
            $table->boolean('append_signature')->default(false);
            $table->enum('category', ['staff', 'third-party', 'system'])->default('system');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['workflow_stage_category_id']);
            $table->index(['department_id']);
            $table->index(['fallback_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_stages');
    }
};
