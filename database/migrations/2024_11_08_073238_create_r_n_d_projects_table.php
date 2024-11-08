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
        Schema::create('r_n_d_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('research_field')->nullable();
            $table->string('research_topic')->nullable();
            $table->longText('research_purpose')->nullable();
            $table->string('research_duration')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->bigInteger('no_of_nigerians')->default(0);
            $table->bigInteger('no_of_expatriates')->default(0);
            $table->decimal('nc_value', 30, 2)->default(0);
            $table->decimal('total_value', 30, 2)->default(0);
            $table->enum('category', ['statutory', 'in-house'])->default('statutory');
            $table->enum('research_type', ['basic', 'applied'])->default('basic');
            $table->enum('research_stream', ['process-improvement', 'product-development'])->default('process-improvement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_n_d_projects');
    }
};
