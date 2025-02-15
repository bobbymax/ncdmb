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
        Schema::table('file_templates', function (Blueprint $table) {
            $table->string('repository')->nullable()->after('component');
            $table->string('response_data_format')->nullable()->after('repository');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_templates', function (Blueprint $table) {
            $table->dropColumn('repository');
            $table->dropColumn('response_data_format');
        });
    }
};
