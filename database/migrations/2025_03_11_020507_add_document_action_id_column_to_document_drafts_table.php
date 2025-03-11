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
            $table->unsignedBigInteger('document_action_id')->default(0)->after('document_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropColumn('document_action_id');
        });
    }
};
