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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id');
            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            $table->bigInteger('airport_id')->default(0);
            $table->bigInteger('departure_city_id')->default(0);
            $table->bigInteger('destination_city_id')->default(0);
            $table->bigInteger('per_diem_category_id')->default(0);
            $table->string('purpose');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('accommodation_type', ['residence', 'non-residence'])->default('non-residence');
            $table->enum('type', ['flight', 'road'])->default('flight');
            $table->decimal('total_amount_spent', 30, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
