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
            // Legal Review Fields
            $table->boolean('legal_review_required')->default(true)->after('procurement_status');
            $table->enum('legal_review_status', [
                'pending',
                'in_review',
                'cleared',
                'rejected',
                'not_required'
            ])->nullable()->after('legal_review_required');
            
            // Legal Clearance Fields
            $table->boolean('legal_clearance_obtained')->default(false)->after('legal_review_status');
            $table->date('legal_clearance_date')->nullable()->after('legal_clearance_obtained');
            $table->string('legal_clearance_reference', 100)->nullable()->after('legal_clearance_date');
            
            // Contract Variations Tracking
            $table->integer('contract_variations_count')->default(0)->after('legal_clearance_reference');
            
            // Dispute Tracking
            $table->boolean('has_active_disputes')->default(false)->after('contract_variations_count');
            
            // Legal Risk Assessment
            $table->enum('legal_risk_level', [
                'low',
                'medium',
                'high',
                'critical'
            ])->nullable()->after('has_active_disputes');
            
            // Last Review Date
            $table->date('last_legal_review_date')->nullable()->after('legal_risk_level');
            
            // Indexes for performance
            $table->index('legal_review_status');
            $table->index('legal_clearance_obtained');
            $table->index('has_active_disputes');
            $table->index('legal_risk_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_contracts', function (Blueprint $table) {
            $table->dropIndex(['legal_review_status']);
            $table->dropIndex(['legal_clearance_obtained']);
            $table->dropIndex(['has_active_disputes']);
            $table->dropIndex(['legal_risk_level']);
            
            $table->dropColumn([
                'legal_review_required',
                'legal_review_status',
                'legal_clearance_obtained',
                'legal_clearance_date',
                'legal_clearance_reference',
                'contract_variations_count',
                'has_active_disputes',
                'legal_risk_level',
                'last_legal_review_date'
            ]);
        });
    }
};

