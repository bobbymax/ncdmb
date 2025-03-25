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
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->unique(
                ['document_id', 'document_draftable_type', 'document_draftable_id', 'version_number'],
                'unique_version_per_document_resource'
            )->after('version_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropUnique('unique_version_per_document_resource');
        });
    }
};
