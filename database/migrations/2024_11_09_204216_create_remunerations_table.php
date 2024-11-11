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
        Schema::create('remunerations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allowance_id');
            $table->foreign('allowance_id')->references('id')->on('allowances')->onDelete('cascade');
            $table->unsignedBigInteger('grade_level_id');
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->onDelete('cascade');
            $table->decimal('amount', 30, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remunerations');
    }
};
