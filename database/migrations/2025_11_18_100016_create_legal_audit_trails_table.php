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
        Schema::create('legal_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->nullable()->constrained('project_contracts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->enum('action_type', [
                'review_created',
                'review_updated',
                'review_approved',
                'review_rejected',
                'clearance_granted',
                'clearance_rejected',
                'variation_created',
                'variation_approved',
                'variation_rejected',
                'compliance_check_performed',
                'dispute_raised',
                'dispute_resolved',
                'document_uploaded',
                'document_signed',
                'other'
            ]);
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('performed_at');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['project_contract_id', 'performed_at']);
            $table->index(['project_id', 'performed_at']);
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_audit_trails');
    }
};

