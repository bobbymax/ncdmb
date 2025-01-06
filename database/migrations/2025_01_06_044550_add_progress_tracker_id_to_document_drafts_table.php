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
            $table->bigInteger('progress_tracker_id')->default(0)->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropColumn('progress_tracker_id');
        });
    }
};
