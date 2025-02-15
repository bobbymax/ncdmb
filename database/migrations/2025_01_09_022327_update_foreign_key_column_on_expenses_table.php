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
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['remuneration_id']);

            $table->bigInteger('remuneration_id')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('remuneration_id')
                ->references('id')->on('remunerations') // Replace with actual related table
                ->onDelete('cascade'); // Adjust the cascade behavior as needed
        });
    }
};
