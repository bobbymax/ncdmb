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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('process_card_id')->nullable()->after('journal_type_id')->constrained('process_cards')->nullOnDelete();
            $table->decimal('debit_amount', 30, 2)->default(0)->after('amount');
            $table->decimal('credit_amount', 30, 2)->default(0)->after('debit_amount');
            $table->decimal('balance', 30, 2)->default(0)->after('credit_amount');
            $table->foreignId('contra_transaction_id')->nullable()->after('balance')->constrained('transactions')->nullOnDelete();
            $table->enum('entry_type', ['opening', 'regular', 'adjusting', 'closing'])->default('regular')->after('contra_transaction_id');
            $table->string('batch_reference')->nullable()->after('entry_type');
            $table->boolean('is_reconciled')->default(false)->after('batch_reference');
            $table->dateTime('reconciled_at')->nullable()->after('is_reconciled');
            $table->foreignId('reconciled_by')->nullable()->after('reconciled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['process_card_id']);
            $table->dropForeign(['contra_transaction_id']);
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn([
                'process_card_id',
                'debit_amount',
                'credit_amount',
                'balance',
                'contra_transaction_id',
                'entry_type',
                'batch_reference',
                'is_reconciled',
                'reconciled_at',
                'reconciled_by'
            ]);
        });
    }
};

