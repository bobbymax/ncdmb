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
        Schema::create('research_facilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('r_n_d_project_id');
            $table->foreign('r_n_d_project_id')->references('id')->on('r_n_d_projects')->onDelete('cascade');
            $table->text('focus_area')->nullable();
            $table->string('average_area')->nullable();
            $table->text('equipment_lacking')->nullable();
            $table->enum('facility_type', ['laboratory', 'workshop'])->default('laboratory');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_facilities');
    }
};
