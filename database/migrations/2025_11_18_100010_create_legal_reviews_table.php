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
        Schema::create('legal_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->nullable()->constrained('project_contracts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->enum('review_type', [
                'contract_review',
                'compliance_check',
                'risk_assessment',
                'variation_review',
                'termination_review',
                'other'
            ])->default('contract_review');
            $table->foreignId('reviewed_by')->constrained('users')->cascadeOnDelete();
            $table->enum('review_status', [
                'pending',
                'in_review',
                'approved',
                'rejected',
                'conditional'
            ])->default('pending');
            $table->date('review_date')->nullable();
            $table->text('legal_opinion')->nullable();
            $table->decimal('compliance_score', 5, 2)->nullable()->comment('Score from 0-100');
            $table->json('risks_identified')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('requires_revision')->default(false);
            $table->text('revision_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('review_status');
            $table->index(['project_contract_id', 'review_status']);
            $table->index(['project_id', 'review_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_reviews');
    }
};

