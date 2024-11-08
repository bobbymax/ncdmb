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
        Schema::create('e_q_succession_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('expatriate_id')->default(0);
            $table->bigInteger('expatriate_level')->default(0);
            $table->bigInteger('understudy_id')->default(0);
            $table->bigInteger('understudy_level')->default(0);
            $table->bigInteger('target_level')->default(0);
            $table->string('competency')->nullable();
            $table->bigInteger('year')->default(0);
            $table->string('period')->nullable();
            $table->text('gap_closure_plan')->nullable();
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_q_succession_plans');
    }
};
