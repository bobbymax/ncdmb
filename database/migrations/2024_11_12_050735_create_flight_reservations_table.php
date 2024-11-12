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
        Schema::create('flight_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('purpose_for_trip');
            $table->decimal('total_proposed_amount', 30, 2)->default(0);
            $table->string('takeoff')->nullable();
            $table->string('destination')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->text('description')->nullable();
            $table->string('approval_memo_path')->nullable();
            $table->string('visa_path')->nullable();
            $table->string('data_page_path')->nullable();
            $table->enum('type', ['staff', 'third-party'])->default('staff');
            $table->enum('status', ['pending', 'registered', 'in-progress', 'itinerary-updated', 'staff-decision', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_reservations');
    }
};
