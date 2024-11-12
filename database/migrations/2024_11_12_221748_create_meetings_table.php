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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('fund_id')->default(0);
            $table->bigInteger('staff_id')->default(0);
            $table->string('code')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration')->default(0);
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->bigInteger('no_of_participants')->default(0);
            $table->enum('arrangement', ['cinema', 'banquet', 'meeting'])->default('meeting');
            $table->boolean('pa_system')->default(false);
            $table->boolean('audio_visual_system')->default(false);
            $table->boolean('internet')->default(false);
            $table->boolean('tea_snacks')->default(false);
            $table->boolean('breakfast')->default(false);
            $table->boolean('lunch')->default(false);
            $table->string('approval_memo_attachment')->nullable();
            $table->text('reason_for_denial')->nullable();
            $table->date('date_approved_or_declined')->nullable();
            $table->decimal('total_approved_amount')->default(0);
            $table->enum('status', ['pending', 'registered', 'in-progress', 'confirm', 'approved', 'rejected', 'cancelled', 'declined'])->default('pending');
            $table->boolean('has_accepted')->default(false);
            $table->boolean('closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
