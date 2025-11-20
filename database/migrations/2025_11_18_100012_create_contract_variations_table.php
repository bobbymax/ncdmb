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
        Schema::create('contract_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->constrained('project_contracts')->cascadeOnDelete();
            $table->enum('variation_type', [
                'price_adjustment',
                'scope_change',
                'time_extension',
                'specification_change',
                'termination',
                'other'
            ])->default('price_adjustment');
            $table->string('variation_reference', 100)->unique();
            $table->decimal('original_value', 20, 2);
            $table->decimal('variation_amount', 20, 2);
            $table->decimal('new_total_value', 20, 2);
            $table->text('reason');
            $table->text('description')->nullable();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->date('initiated_date');
            $table->foreignId('legal_review_id')->nullable()->constrained('legal_reviews')->nullOnDelete();
            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
                'conditional'
            ])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('variation_document_url', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('approval_status');
            $table->index(['project_contract_id', 'approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_variations');
    }
};

