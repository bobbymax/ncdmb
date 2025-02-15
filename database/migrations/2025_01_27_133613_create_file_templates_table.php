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
        Schema::create('file_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->unique();
            $table->string('service');
            $table->string('component');
            $table->text('tagline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_templates');
    }
};
