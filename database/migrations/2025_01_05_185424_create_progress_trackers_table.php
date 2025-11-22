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
        Schema::create('progress_trackers', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 36)->unique();
            
            // Foreign keys - properly defined
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('internal_process_id')->nullable();
            $table->unsignedBigInteger('carder_id')->nullable();
            $table->unsignedBigInteger('signatory_id')->nullable();
            $table->unsignedBigInteger('process_card_id')->nullable();
            
            // Core fields
            $table->integer('order')->default(0);
            $table->enum('permission', ['r', 'rw', 'rwx'])->default('r');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['workflow_id', 'workflow_stage_id']);
            $table->index(['group_id']);
            $table->index(['department_id']);
            $table->index(['signatory_id']);
            $table->index('permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_trackers');
    }
};
