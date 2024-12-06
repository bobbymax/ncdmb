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
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_stage_id');
            $table->foreign('workflow_stage_id')->references('id')->on('workflow_stages')->onDelete('cascade');
            $table->string('name');
            $table->string('label')->unique();
            $table->text('description')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('low');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
