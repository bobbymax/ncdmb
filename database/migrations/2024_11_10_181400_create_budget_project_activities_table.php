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
        Schema::create('budget_project_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_plan_id');
            $table->foreign('budget_plan_id')->references('id')->on('budget_plans')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('activity_title');
            $table->text('description')->nullable();
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_completion_date')->nullable();
            $table->decimal('proposed_amount', 30, 2)->default(0);
            $table->decimal('approved_amount', 30, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_project_activities');
    }
};
