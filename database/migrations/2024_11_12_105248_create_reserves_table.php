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
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->bigInteger('destination_fund_id')->default(0);
            $table->bigInteger('expenditure_id')->default(0);
            $table->bigInteger('staff_id')->default(0);
            $table->decimal('total_reserved_amount', 30, 2)->default(0);
            $table->enum('status', ['pending', 'secured', 'released', 'reversed', 'rejected'])->default('pending');
            $table->string('approval_memo_path')->nullable();
            $table->string('approval_reversal_memo_path')->nullable();
            $table->date('date_reserved_approval_or_denial')->nullable();
            $table->boolean('fulfilled')->default(false);
            $table->boolean('secured')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reservable_id');
            $table->string('reservable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};
