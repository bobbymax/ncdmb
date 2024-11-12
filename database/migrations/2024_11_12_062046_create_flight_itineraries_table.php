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
        Schema::create('flight_itineraries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flight_reservation_id');
            $table->foreign('flight_reservation_id')->references('id')->on('flight_reservations')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('mandate_id')->default(0);
            $table->string('airline')->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();
            $table->dateTime('takeoff_departure_date')->nullable();
            $table->dateTime('takeoff_arrival_date')->nullable();
            $table->dateTime('return_departure_date')->nullable();
            $table->dateTime('return_arrival_date')->nullable();
            $table->bigInteger('departure_layovers')->default(0);
            $table->bigInteger('return_layovers')->default(0);
            $table->string('flight_ticket_path')->nullable();
            $table->decimal('total_ticket_price', 30, 2)->default(0);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_itineraries');
    }
};
