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
        Schema::table('document_panels', function (Blueprint $table) {
            $table->string('document_status')->nullable()->after('component_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_panels', function (Blueprint $table) {
            $table->dropColumn('document_status');
        });
    }
};
