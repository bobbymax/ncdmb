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
        Schema::create('research_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('r_n_d_project_id');
            $table->foreign('r_n_d_project_id')->references('id')->on('r_n_d_projects')->onDelete('cascade');
            $table->string('item');
            $table->bigInteger('year')->default(0);
            $table->decimal('planned_amount', 30, 2)->default(0);
            $table->decimal('actual_amount', 30, 2)->default(0);
            $table->enum('item_origin', ['nigerian', 'foreign'])->default('nigerian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_budgets');
    }
};
