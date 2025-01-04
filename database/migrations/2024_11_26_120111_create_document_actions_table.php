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
        Schema::create('document_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->unique();
            $table->string('button_text')->nullable();
            $table->string('icon')->nullable();
            $table->enum('variant', ['primary', 'info', 'success', 'warning', 'danger', 'dark'])->default('primary');
            $table->string('status')->nullable();
            $table->enum('process_status', ['next', 'stall', 'goto', 'end', 'complete'])->default('next');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_actions');
    }
};
