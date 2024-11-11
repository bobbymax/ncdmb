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
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_budget_head_id');
            $table->foreign('sub_budget_head_id')->references('id')->on('sub_budget_heads')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('budget_code_id');
            $table->foreign('budget_code_id')->references('id')->on('budget_codes')->onDelete('cascade');
            $table->decimal('total_approved_amount', 30, 2)->default(0);
            $table->decimal('total_expected_spent_amount', 30, 2)->default(0);
            $table->decimal('total_actual_spent_amount', 30, 2)->default(0);
            $table->decimal('total_booked_balance', 30, 2)->default(0);
            $table->decimal('total_actual_balance', 30, 2)->default(0);
            $table->decimal('total_reserved_amount', 30, 2)->default(0);
            $table->bigInteger('budget_year')->default(0);
            $table->boolean('is_exhausted')->default(false);
            $table->boolean('is_logistics')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
