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
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->dropColumn('no_of_payments');
            $table->dropColumn('beneficiary');
            $table->dropColumn('total_payable_amount');
            $table->dropColumn('total_approved_payable_amount');
            $table->string('status')->change();
            $table->enum('type', ['staff', 'third-party'])->default('staff')->change();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->enum('type', ['staff-payment', 'third-party-payment'])->default('staff-payment')->change();
            $table->enum('status', ['pending', 'dispatched', 'paid', 'reversed'])->default('pending')->change();
            $table->decimal('total_approved_payable_amount', 30, 2)->default(0)->after('description');
            $table->decimal('total_payable_amount', 30, 2)->default(0)->after('code');
            $table->string('beneficiary')->nullable()->after('total_payable_amount');
            $table->integer('no_of_payments')->default(0)->after('beneficiary');
        });
    }
};
