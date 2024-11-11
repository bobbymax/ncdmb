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
        Schema::create('store_supplies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('project_contract_id');
            $table->foreign('project_contract_id')->references('id')->on('project_contracts')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('product_measurement_id');
            $table->foreign('product_measurement_id')->references('id')->on('product_measurements')->onDelete('cascade');
            $table->bigInteger('quantity')->default(0);
            $table->decimal('unit_price', 30, 2)->default(0);
            $table->decimal('total_price', 30, 2)->default(0);
            $table->date('delivery_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->enum('period_published', ['Q1', 'Q2', 'Q3', 'Q4'])->default('Q1');
            $table->enum('status', ['unfulfilled', 'fulfilled', 'partial', 'rejected'])->default('unfulfilled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_supplies');
    }
};
