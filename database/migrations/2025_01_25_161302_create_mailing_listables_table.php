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
        Schema::create('mailing_listables', function (Blueprint $table) {
            $table->unsignedBigInteger('mailing_list_id');
            $table->unsignedBigInteger('mailing_listable_id');
            $table->string('mailing_listable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailing_listables');
    }
};
