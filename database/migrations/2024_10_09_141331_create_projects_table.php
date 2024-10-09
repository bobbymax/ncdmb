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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('operator_id')->unsigned();
            $table->foreign('operator_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('approved_amount', 30, 2)->default(0);
            $table->enum('status', ['pending', 'in-progress', 'in-review', 'awaiting-response', 'verified', 'completed', 'overdue'])->default('pending');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
