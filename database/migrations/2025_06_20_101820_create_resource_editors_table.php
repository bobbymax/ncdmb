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
        Schema::create('resource_editors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->unsignedBigInteger('workflow_id');
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->unsignedBigInteger('workflow_stage_id');
            $table->foreign('workflow_stage_id')->references('id')->on('workflow_stages')->onDelete('cascade');
            $table->string('service_name');
            $table->string('resource_column_name');
            $table->enum('permission', ['r', 'rw', 'rwx'])->default('rw');
            $table->enum('service_update', ['d', 'dr', 'drn'])->default('dr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_editors');
    }
};
