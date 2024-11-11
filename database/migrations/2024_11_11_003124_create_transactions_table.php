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
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('expenditure_id');
            $table->foreign('expenditure_id')->references('id')->on('expenditures')->onDelete('cascade');
            $table->unsignedBigInteger('payment_batch_id');
            $table->foreign('payment_batch_id')->references('id')->on('payment_batches')->onDelete('cascade');
            $table->bigInteger('staff_id')->default(0);
            $table->bigInteger('vendor_id')->default(0);
            $table->enum('currency', ['NGN', 'USD', 'EUR', 'GBP'])->default('NGN');
            $table->decimal('transaction_amount', 30, 2)->default(0);
            $table->enum('type', ['debit', 'credit'])->default('debit');
            $table->date('transaction_date');
            $table->date('date_posted')->nullable();
            $table->bigInteger('budget_year')->default(0);
            $table->boolean('is_posted')->default(false);
            $table->text('query_note')->nullable();
            $table->text('query_response')->nullable();
            $table->enum('stage', ['budget-office', 'treasury', 'audit'])->default('budget-office');
            $table->enum('status', ['in-progress', 'queried', 'cleared', 'altered'])->default('in-progress');
            $table->timestamps();
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
