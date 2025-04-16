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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->nullOnDelete();
            $table->string('reference', 192)->unique();
            $table->enum('type', ['debit', 'credit'])->default('debit');
            $table->decimal('amount', 30, 2)->default(0);
            $table->string('narration')->nullable();
            $table->unsignedBigInteger('beneficiary_id')->nullable();
            $table->string('beneficiary_type')->nullable();
            $table->enum('payment_method', ['bank-transfer', 'cheque', 'cash', 'cheque-number'])->default('bank-transfer');
            $table->enum('currency', ['USD', 'EUR', 'NGN', 'GBP', 'YEN'])->default('NGN');
            $table->dateTime('posted_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
