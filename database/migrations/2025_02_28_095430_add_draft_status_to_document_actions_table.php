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
        Schema::table('document_actions', function (Blueprint $table) {
            $table->string('draft_status')->nullable()->after('button_text');
            $table->enum('resource_type', ['searchable', 'classified', 'private', 'archived', 'computed', 'generated', 'report', 'other'])->default('searchable')->after('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_actions', function (Blueprint $table) {
            $table->dropColumn('draft_status');
            $table->dropColumn('resource_type');
        });
    }
};
