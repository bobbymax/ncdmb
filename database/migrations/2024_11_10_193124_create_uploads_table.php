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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->bigInteger('size')->default(0);
            $table->string('mime_type')->default('image/jpeg');
            $table->string('extension')->default('jpg');
            $table->unsignedBigInteger('uploadable_id');
            $table->string('uploadable_type');
            $table->timestamps();
            
            // Index for polymorphic relationship
            $table->index(['uploadable_id', 'uploadable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
