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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('milestoneable_id');
            $table->string('milestoneable_type');
            $table->text('description');
            $table->decimal('percentage_completion', 5, 2)->default(0);
            $table->integer('duration')->default(0);
            $table->enum('frequency', ['days', 'weeks', 'months', 'years'])->default('days');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
