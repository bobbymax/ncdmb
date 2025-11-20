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
        Schema::create('legal_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->constrained('project_contracts')->cascadeOnDelete();
            $table->enum('clearance_type', [
                'pre_award',
                'pre_signing',
                'variation',
                'termination',
                'other'
            ])->default('pre_signing');
            $table->enum('clearance_status', [
                'pending',
                'cleared',
                'rejected',
                'conditional',
                'expired'
            ])->default('pending');
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('clearance_date')->nullable();
            $table->string('clearance_reference', 100)->unique()->nullable();
            $table->json('conditions')->nullable()->comment('Any conditions attached to clearance');
            $table->date('expiry_date')->nullable()->comment('If conditional clearance');
            $table->json('compliance_requirements')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clearance_status');
            $table->index(['project_contract_id', 'clearance_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_clearances');
    }
};

