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
        Schema::create('board_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('contractor_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

            $table->string('title');
            $table->date('approval_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();

            $table->string('ownership_type')->nullable();
            $table->bigInteger('percentage_committed')->default(0);
            $table->decimal('total_amount', 30, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_projects');
    }
};
