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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - properly defined
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('document_category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained('workflows')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('document_reference_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('document_action_id')->nullable()->constrained('document_actions')->nullOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('funds')->nullOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('progress_tracker_id')->nullable();
            $table->unsignedBigInteger('threshold_id')->nullable();
            
            // Polymorphic relationship
            $table->unsignedBigInteger('documentable_id');
            $table->string('documentable_type');
            
            // Core fields
            $table->string('title');
            $table->string('ref')->unique();
            $table->longText('description')->nullable();
            
            // Status and type
            $table->string('status')->nullable(); // changed from enum to string
            $table->enum('type', ['staff', 'third-party'])->default('staff');
            
            // JSON fields
            $table->json('config')->nullable();
            $table->json('contents')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('uploaded_requirements')->nullable();
            $table->json('preferences')->nullable();
            $table->json('watchers')->nullable();
            $table->json('activities')->nullable();
            $table->string('pointer')->nullable();
            
            // Amount fields
            $table->decimal('approved_amount', 30, 2)->default(0);
            $table->decimal('sub_total_amount', 30, 2)->default(0);
            $table->decimal('admin_fee_amount', 30, 2)->default(0);
            $table->decimal('vat_amount', 30, 2)->default(0);
            $table->decimal('variation_amount', 30, 2)->default(0);
            
            // Year - changed from bigInteger default(0) to year nullable
            $table->year('budget_year')->nullable();
            
            // Metadata
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['documentable_id', 'documentable_type']);
            $table->index(['user_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['document_category_id']);
            $table->index(['workflow_id']);
            $table->index(['progress_tracker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
