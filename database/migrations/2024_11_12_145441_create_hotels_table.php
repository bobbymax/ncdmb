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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->unique();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('street_one')->nullable();
            $table->string('street_two')->nullable();
            $table->string('house_no')->nullable();
            $table->string('representative')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('not_active')->default(false);
            $table->decimal('unit_price_per_night', 30, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
