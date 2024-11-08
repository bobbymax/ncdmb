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
        Schema::create('research_team_developments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('r_n_d_project_id');
            $table->foreign('r_n_d_project_id')->references('id')->on('r_n_d_projects')->onDelete('cascade');
            $table->unsignedBigInteger('c_d_activity_id');
            $table->foreign('c_d_activity_id')->references('id')->on('c_d_activities')->onDelete('cascade');
            $table->string('title');
            $table->bigInteger('no_of_persons')->default(0);
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_team_developments');
    }
};
