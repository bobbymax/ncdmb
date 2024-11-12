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
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('staff_id')->default(0);
            $table->bigInteger('mandate_id')->default(0);
            $table->string('code')->unique();
            $table->string('approval_memo_attachment')->nullable();
            $table->string('hotel_booking_attachment')->nullable();
            $table->decimal('cost_no_of_nights', 30, 2)->default(0);
            $table->decimal('total_approved_amount', 30 ,2)->default(0);
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->time('check_in_time')->nullable();
            $table->string('purpose')->nullable();
            $table->text('instructions')->nullable();
            $table->text('remark')->nullable();
            $table->enum('status', ['pending', 'in-progress', 'decision', 'approved', 'rejected', 'denied'])->default('pending');
            $table->boolean('is_accepted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
};
