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
        Schema::create('board_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_project_activity_id');
            $table->foreign('budget_project_activity_id')->references('id')->on('budget_project_activities')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->decimal('total_proposed_value', 30, 2)->default(0);
            $table->decimal('total_revised_value', 30, 2)->default(0);
            $table->decimal('total_approved_value', 30, 2)->default(0);
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_completion_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('approval_threshold', ['work-order', 'tenders', 'minister', 'fec', 'other'])->default('work-order');
            $table->enum('status', ['pending', 'completed', 'overdue'])->default('pending');
            $table->boolean('is_archived')->default(false);
            $table->bigInteger('budget_year')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_projects');
    }
};
