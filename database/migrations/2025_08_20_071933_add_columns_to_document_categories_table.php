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
        Schema::table('document_categories', function (Blueprint $table) {
            $table->json('config')->nullable()->after('service');
            $table->json('workflow')->nullable()->after('config');
            $table->json('content')->nullable()->after('workflow');
            $table->json('meta_data')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropColumn(['config', 'workflow', 'content', 'meta_data']);
        });
    }
};
