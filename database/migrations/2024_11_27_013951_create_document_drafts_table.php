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
        Schema::create('document_drafts', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - properly defined
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('current_workflow_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('document_action_id')->nullable()->constrained('document_actions')->nullOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('progress_tracker_id')->nullable();
            $table->unsignedBigInteger('carder_id')->nullable();
            
            // Core fields
            $table->string('status')->nullable();
            $table->unsignedBigInteger('version_number')->nullable();
            
            // Amount fields
            $table->decimal('amount', 30, 2)->default(0);
            $table->decimal('taxable_amount', 30, 2)->default(0);
            $table->decimal('sub_total_amount', 30, 2)->default(0);
            $table->decimal('vat_amount', 30, 2)->default(0);
            
            // Permission and remarks
            $table->enum('permission', ['r', 'rw', 'rwx'])->default('r');
            $table->longText('remark')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint for versioning
            $table->unique(
                ['document_id', 'version_number'],
                'unique_version_per_document'
            );
            
            // Indexes for performance
            $table->index(['document_id', 'status']);
            $table->index(['group_id', 'status']);
            $table->index(['current_workflow_stage_id']);
            $table->index(['progress_tracker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_drafts');
    }
};
