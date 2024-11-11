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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('code')->unique();
            $table->text('purpose');
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('total_amount_spent', 30, 2)->default(0);
            $table->decimal('total_amount_approved', 30, 2)->default(0);
            $table->decimal('total_amount_retired', 30, 2)->default(0);
            $table->enum('type', ['claim', 'retirement'])->default('claim');
            $table->enum('category', ['residence', 'non-residence'])->default('non-residence');
            $table->enum('status', ['pending', 'registered', 'raised', 'batched', 'queried', 'paid', 'draft'])->default('pending');
            $table->boolean('retired')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
