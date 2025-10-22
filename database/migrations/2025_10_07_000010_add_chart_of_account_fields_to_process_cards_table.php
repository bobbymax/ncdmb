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
        Schema::table('process_cards', function (Blueprint $table) {
            $table->foreignId('debit_account_id')->nullable()->after('ledger_id')->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('credit_account_id')->nullable()->after('debit_account_id')->constrained('chart_of_accounts')->nullOnDelete();
            $table->integer('execution_order')->default(0)->after('component');
            $table->json('validation_rules')->nullable()->after('rules');
            $table->boolean('auto_reconcile')->default(false)->after('validation_rules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_cards', function (Blueprint $table) {
            $table->dropForeign(['debit_account_id']);
            $table->dropForeign(['credit_account_id']);
            $table->dropColumn([
                'debit_account_id',
                'credit_account_id',
                'execution_order',
                'validation_rules',
                'auto_reconcile'
            ]);
        });
    }
};

