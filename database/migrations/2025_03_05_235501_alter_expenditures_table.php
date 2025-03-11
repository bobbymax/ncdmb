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
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('batch_id');
            $table->dropColumn('vendor_id');
            $table->dropColumn('staff_id');
            $table->dropColumn('claim_id');
            $table->dropColumn('project_milestone_id');
            $table->dropColumn('beneficiary_name');
            $table->renameColumn('payment_description', 'purpose');
            $table->dropColumn('total_amount_raised');
            $table->renameColumn('total_approved_amount', 'amount');
            $table->dropColumn('flag');
            $table->dropColumn('stage');
            $table->bigInteger('document_draft_id')->default(0)->after('fund_id');
            $table->string('status')->default('pending')->change();
            $table->enum('currency', ['NGN', 'USD', 'GBP', 'YEN', 'EUR'])->default('NGN')->after('status');
            $table->decimal('cbn_current_rate', 15, 2)->default(0)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->bigInteger('batch_id')->default(0)->after('fund_id');
            $table->bigInteger('vendor_id')->default(0)->after('batch_id');
            $table->bigInteger('staff_id')->default(0)->after('vendor_id');
            $table->bigInteger('claim_id')->default(0)->after('staff_id');
            $table->bigInteger('project_milestone_id')->default(0)->after('claim_id');
            $table->string('beneficiary_name')->default(0)->after('code');
            $table->renameColumn('purpose', 'payment_description');
            $table->decimal('total_amount_raised', 30, 2)->default(0)->after('payment_description');
            $table->renameColumn('amount', 'total_approved_amount');
            $table->enum('flag', ['debit', 'credit'])->default('debit')->after('total_amount_raised');
            $table->enum('stage', ['raised', 'batched', 'dispatched', 'budget-office', 'treasury', 'audit', 'posting'])->default('raised')->after('flag');
            $table->dropColumn('document_draft_id');
            $table->enum('status', ['pending', 'cleared', 'queried', 'paid', 'reversed', 'refunded'])->default('pending')->change();
            $table->dropColumn('currency');
            $table->dropColumn('cbn_current_rate');
        });
    }
};
