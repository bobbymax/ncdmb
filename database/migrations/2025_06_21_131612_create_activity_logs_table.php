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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');

            $table->foreignId('document_id')
                ->nullable()
                ->constrained('documents')
                ->nullOnDelete();
            $table->foreignId('document_action_id')
                ->nullable()
                ->constrained('document_actions')
                ->nullOnDelete();

            $table->string('action_category');
            $table->text('description');
            $table->unsignedBigInteger('logable_id')->nullable();
            $table->string('logable_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
