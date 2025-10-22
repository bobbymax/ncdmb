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
        Schema::create('fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained('funds')->cascadeOnDelete();
            $table->foreignId('process_card_id')->nullable()->constrained('process_cards')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('transaction_type', [
                'allocation', 
                'reservation', 
                'expenditure', 
                'payment', 
                'refund', 
                'reversal', 
                'adjustment',
                'transfer'
            ])->default('expenditure');
            $table->enum('movement', ['debit', 'credit'])->default('debit');
            $table->decimal('amount', 30, 2)->default(0);
            $table->decimal('balance_before', 30, 2)->default(0);
            $table->decimal('balance_after', 30, 2)->default(0);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable();
            $table->text('narration');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reversed_at')->nullable();
            $table->timestamps();
            
            $table->index(['source_id', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transactions');
    }
};

