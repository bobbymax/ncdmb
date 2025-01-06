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
        Schema::create('progress_trackers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->unsignedBigInteger('workflow_stage_id');
            $table->foreign('workflow_stage_id')->references('id')->on('workflow_stages')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->dateTime('date_completed')->nullable();
            $table->enum('status', ['pending', 'passed', 'stalled', 'failed'])->default('pending');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_trackers');
    }
};
