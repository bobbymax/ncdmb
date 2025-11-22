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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_no', 192)->unique()->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained();
            $table->text('description');
            $table->dateTime('transaction_date')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('journalable_id')->nullable();
            $table->string('journalable_type')->nullable();
            $table->string('status')->default('logged');
            $table->timestamps();
            $table->softDeletes();
            
            // Index for polymorphic relationship
            $table->index(['journalable_id', 'journalable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
