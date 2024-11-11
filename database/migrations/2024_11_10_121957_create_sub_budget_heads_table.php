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
        Schema::create('sub_budget_heads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_head_id');
            $table->foreign('budget_head_id')->references('id')->on('budget_heads')->onDelete('cascade');
            $table->string('name');
            $table->string('label')->unique();
            $table->enum('type', ['capital', 'recurrent', 'personnel'])->default('capital');
            $table->boolean('is_logistics')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_budget_heads');
    }
};
