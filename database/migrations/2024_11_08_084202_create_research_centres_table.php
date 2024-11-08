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
        Schema::create('research_centres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('r_n_d_project_id');
            $table->foreign('r_n_d_project_id')->references('id')->on('r_n_d_projects')->onDelete('cascade');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->enum('category', ['university', 'research-centre', 'other'])->default('university');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_centres');
    }
};
