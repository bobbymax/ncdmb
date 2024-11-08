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
        Schema::create('procured_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->foreign('contractor_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('material_type_id');
            $table->foreign('material_type_id')->references('id')->on('material_types')->onDelete('cascade');
            $table->longText('item_description')->nullable();
            $table->bigInteger('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->string('oem_name')->nullable();
            $table->string('oem_origin')->nullable();
            $table->bigInteger('year')->default(0);
            $table->string('period')->nullable();
            $table->decimal('nc_value', 30, 2)->default(0);
            $table->decimal('total_value', 30, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procured_materials');
    }
};
