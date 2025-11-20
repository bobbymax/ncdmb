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
        Schema::create('legal_compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->constrained('project_contracts')->cascadeOnDelete();
            $table->enum('compliance_type', [
                'procurement_act',
                'fiscal_responsibility',
                'public_accounts',
                'company_law',
                'tax_compliance',
                'other'
            ])->default('procurement_act');
            $table->enum('check_status', [
                'pending',
                'passed',
                'failed',
                'conditional'
            ])->default('pending');
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();
            $table->date('check_date');
            $table->text('findings')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->decimal('compliance_score', 5, 2)->nullable()->comment('Score from 0-100');
            $table->boolean('requires_remediation')->default(false);
            $table->text('remediation_plan')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('check_status');
            $table->index(['project_contract_id', 'check_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_compliance_checks');
    }
};

