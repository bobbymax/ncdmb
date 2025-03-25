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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_type_id');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('title');
            $table->string('component');
            $table->string('chart_type')->nullable();
            $table->boolean('is_active')->default(false);
            $table->enum('response', ['resource', 'collection'])->default('collection');
            $table->enum('type', ['box', 'card', 'chart', 'banner', 'breadcrumb'])->default('box');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
