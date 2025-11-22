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
        Schema::create('payment_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->string('code')->unique();
            $table->integer('no_of_payments')->default(0);
            $table->string('beneficiary')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_payable_amount', 30, 2)->default(0);
            $table->decimal('total_approved_payable_amount', 30, 2)->default(0);
            $table->year('budget_year')->nullable(); // Changed from bigInteger default(0) to year nullable
            $table->enum('type', ['staff-payment', 'third-party-payment'])->default('staff-payment');
            $table->enum('status', ['pending', 'dispatched', 'paid', 'reversed'])->default('pending');
            $table->timestamps();
            // Note: softDeletes() is added in 2025_03_30_073049_change_columns_on_payment_batches_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_batches');
    }
};
