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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->string('name');
            $table->string('room_no')->nullable();
            $table->integer('floor')->default(0);
            $table->bigInteger('max_capacity')->default(0);
            $table->enum('type', ['hall', 'room', 'main'])->default('room');
            $table->enum('area', ['wing-a', 'wing-b', 'conference-centre', 'other'])->default('other');
            $table->enum('status', ['available', 'occupied', 'in-maintenance', 'decommissioned'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
