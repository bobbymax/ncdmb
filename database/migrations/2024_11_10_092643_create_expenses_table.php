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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id');
            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            $table->unsignedBigInteger('remuneration_id')->default(0);
            $table->string('identifier')->unique()->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('parent_id')->default(0);
            $table->bigInteger('allowance_id')->default(0);
            $table->decimal('total_distance_covered', 8, 2)->default(0);
            $table->decimal('unit_price', 30, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->bigInteger('no_of_days')->default(0);
            $table->decimal('total_amount_spent', 30, 2)->default(0);
            $table->decimal('total_amount_paid', 30, 2)->default(0);
            $table->enum('status', ['pending', 'cleared', 'altered', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
