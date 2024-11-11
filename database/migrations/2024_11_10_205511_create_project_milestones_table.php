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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('board_project_id');
            $table->foreign('board_project_id')->references('id')->on('board_projects')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->text('description');
            $table->decimal('total_milestone_payable_amount', 30, 2)->default(0);
            $table->bigInteger('percentage_project_completion')->default(0);
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('stage', ['work-order', 'jcc', 'payment-mandate', 'raise-payment'])->default('work-order');
            $table->enum('status', ['pending', 'in-progress', 'completed', 'overdue', 'cancelled'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
