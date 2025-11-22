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
        Schema::create('account_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('ledger_id')->constrained('ledgers')->cascadeOnDelete();
            $table->foreignId('process_card_id')->nullable()->constrained('process_cards')->nullOnDelete();
            $table->decimal('debit', 30, 2)->default(0);
            $table->decimal('credit', 30, 2)->default(0);
            $table->decimal('running_balance', 30, 2)->default(0);
            $table->string('posting_reference')->unique();
            $table->enum('posting_type', ['manual', 'auto', 'reversed', 'adjustment'])->default('auto');
            $table->dateTime('posted_at');
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_postings');
    }
};

