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
        Schema::create('board_project_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('board_project_id');
            $table->foreign('board_project_id')->references('id')->on('board_projects')->onDelete('cascade');

            $table->longText('activity')->nullable();
            $table->bigInteger('year')->default(0);
            $table->string('period')->nullable();
            $table->bigInteger('no_of_personnel')->default(0);
            $table->string('man_hours')->nullable();
            $table->decimal('amount_spent', 30, 2)->default(0);
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
        Schema::dropIfExists('board_project_activities');
    }
};
