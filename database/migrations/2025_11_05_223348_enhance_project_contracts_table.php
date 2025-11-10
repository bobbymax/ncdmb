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
        Schema::table('project_contracts', function (Blueprint $table) {
            // Add project_id to link directly to projects
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->cascadeOnDelete();
            
            // Contract Details
            $table->string('contract_reference', 100)->unique()->nullable()->after('project_id');
            $table->decimal('contract_value', 20, 2)->default(0)->after('contract_reference');
            $table->decimal('vat_amount', 20, 2)->default(0)->after('contract_value');
            
            // Award Details
            $table->date('award_date')->nullable()->after('date_of_acceptance');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->integer('contract_duration_months')->nullable();
            
            // Performance Security
            $table->boolean('performance_bond_required')->default(true);
            $table->decimal('performance_bond_percentage', 5, 2)->default(10.00);
            $table->decimal('performance_bond_amount', 20, 2)->nullable();
            $table->boolean('performance_bond_submitted')->default(false);
            $table->string('performance_bond_reference', 100)->nullable();
            
            // Advance Payment
            $table->boolean('advance_payment_allowed')->default(false);
            $table->decimal('advance_payment_percentage', 5, 2)->default(0);
            $table->decimal('advance_payment_amount', 20, 2)->default(0);
            
            // Approval
            $table->date('tenders_board_approval_date')->nullable();
            $table->string('tenders_board_reference', 100)->nullable();
            
            // Publication
            $table->timestamp('published_at')->nullable();
            $table->string('publication_reference', 100)->nullable();
            
            // Signing
            $table->boolean('contract_signed')->default(false);
            $table->date('contract_signed_date')->nullable();
            $table->string('contract_document_url', 500)->nullable();
            
            // Standstill Period
            $table->date('standstill_start_date')->nullable();
            $table->date('standstill_end_date')->nullable();
            
            // Enhanced Status (keeping old status for backward compatibility)
            $table->enum('procurement_status', [
                'draft', 'recommended', 'approved', 'published',
                'signed', 'active', 'suspended', 'completed', 'terminated'
            ])->nullable()->after('status');
            
            $table->index('procurement_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_contracts', function (Blueprint $table) {
            $table->dropIndex(['procurement_status']);
            $table->dropForeign(['project_id']);
            
            $table->dropColumn([
                'project_id', 'contract_reference', 'contract_value', 'vat_amount',
                'award_date', 'contract_start_date', 'contract_end_date', 
                'contract_duration_months', 'performance_bond_required',
                'performance_bond_percentage', 'performance_bond_amount',
                'performance_bond_submitted', 'performance_bond_reference',
                'advance_payment_allowed', 'advance_payment_percentage',
                'advance_payment_amount', 'tenders_board_approval_date',
                'tenders_board_reference', 'published_at', 'publication_reference',
                'contract_signed', 'contract_signed_date', 'contract_document_url',
                'standstill_start_date', 'standstill_end_date', 'procurement_status'
            ]);
        });
    }
};
