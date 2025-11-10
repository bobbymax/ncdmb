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
        Schema::create('project_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('bid_invitation_id')->constrained('project_bid_invitations')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            
            // Bid Details
            $table->string('bid_reference', 100)->unique();
            $table->decimal('bid_amount', 20, 2);
            $table->string('bid_currency', 10)->default('NGN');
            
            // Submission
            $table->timestamp('submitted_at')->nullable();
            $table->enum('submission_method', ['physical', 'electronic', 'hybrid'])->default('physical');
            $table->foreignId('received_by')->nullable()->constrained('users');
            
            // Bid Security
            $table->boolean('bid_security_submitted')->default(false);
            $table->enum('bid_security_type', ['bank_guarantee', 'insurance_bond', 'cash'])->nullable();
            $table->string('bid_security_reference', 100)->nullable();
            
            // Opening
            $table->timestamp('opened_at')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users');
            
            // Compliance
            $table->boolean('is_administratively_compliant')->nullable();
            $table->text('administrative_notes')->nullable();
            
            // Evaluation
            $table->decimal('technical_score', 5, 2)->nullable();
            $table->enum('technical_status', ['pending', 'passed', 'failed'])->nullable();
            $table->text('technical_notes')->nullable();
            
            $table->decimal('financial_score', 5, 2)->nullable();
            $table->boolean('is_financially_responsive')->nullable();
            $table->text('financial_notes')->nullable();
            
            $table->decimal('combined_score', 5, 2)->nullable();
            $table->integer('ranking')->nullable();
            
            // Status
            $table->enum('status', [
                'submitted', 'opened', 'responsive', 'non_responsive',
                'under_evaluation', 'evaluated', 'disqualified',
                'recommended', 'awarded', 'not_awarded'
            ])->default('submitted');
            $table->text('disqualification_reason')->nullable();
            
            // Documents
            $table->json('bid_documents')->nullable(); // [{document_name, file_url, uploaded_at}]
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('ranking');
            $table->unique(['project_id', 'vendor_id'], 'unique_project_vendor_bid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_bids');
    }
};
