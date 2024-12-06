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
        Schema::create('document_typeables', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('document_typeable_id');
            $table->string('document_typeable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_typeables');
    }
};
