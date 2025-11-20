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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->constrained('project_contracts')->cascadeOnDelete();
            $table->enum('document_type', [
                'contract_draft',
                'signed_contract',
                'addendum',
                'legal_opinion',
                'clearance_certificate',
                'variation_order',
                'termination_notice',
                'other'
            ])->default('contract_draft');
            $table->string('document_name');
            $table->string('document_url', 500);
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at');
            $table->boolean('is_current')->default(true);
            $table->boolean('requires_signature')->default(false);
            $table->json('signed_by')->nullable()->comment('Array of signatory user IDs');
            $table->timestamp('signed_at')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_contract_id', 'document_type']);
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};

