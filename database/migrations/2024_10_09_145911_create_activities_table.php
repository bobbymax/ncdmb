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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_scope_id');
            $table->foreign('project_scope_id')->references('id')->on('project_scopes')->onDelete('cascade');

            $table->bigInteger('year')->default(0);
            $table->string('month')->nullable();
            $table->decimal('total_value_spent', 30, 2)->default(0);
            $table->decimal('nc_value_spent', 30, 2)->default(0);
            $table->bigInteger('no_of_nigerians')->default(0);
            $table->bigInteger('no_of_expatriates')->default(0);
            $table->bigInteger('ngn_man_hrs')->default(0);
            $table->bigInteger('expatriates_man_hrs')->default(0);
            $table->longText('remarks')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
