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
        Schema::create('lif_institution_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lif_institution_id');
            $table->foreign('lif_institution_id')->references('id')->on('lif_institutions')->onDelete('cascade');
            $table->unsignedBigInteger('lif_service_id');
            $table->foreign('lif_service_id')->references('id')->on('lif_services')->onDelete('cascade');
            $table->unsignedBigInteger('contractor_id');
            $table->foreign('contractor_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lif_institution_services');
    }
};
