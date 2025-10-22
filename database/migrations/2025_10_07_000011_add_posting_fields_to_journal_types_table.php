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
        Schema::table('journal_types', function (Blueprint $table) {
            $table->foreignId('debit_account_id')->nullable()->after('ledger_id')->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('credit_account_id')->nullable()->after('debit_account_id')->constrained('chart_of_accounts')->nullOnDelete();
            $table->boolean('auto_post_to_ledger')->default(false)->after('auto_generate_entries');
            $table->boolean('requires_approval')->default(false)->after('auto_post_to_ledger');
            $table->json('posting_rules')->nullable()->after('requires_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_types', function (Blueprint $table) {
            $table->dropForeign(['debit_account_id']);
            $table->dropForeign(['credit_account_id']);
            $table->dropColumn([
                'debit_account_id',
                'credit_account_id',
                'auto_post_to_ledger',
                'requires_approval',
                'posting_rules'
            ]);
        });
    }
};

