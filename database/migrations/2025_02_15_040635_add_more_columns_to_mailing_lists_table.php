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
        Schema::table('mailing_lists', function (Blueprint $table) {
            // Note: This migration was incorrectly removing the foreign key constraint
            // The group_id should remain as a foreign key for data integrity
            // If you need to make it nullable, use: $table->foreignId('group_id')->nullable()->change();
            // For now, we'll keep the foreign key relationship intact
            // If this migration was meant to handle a specific case, it should be reviewed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mailing_lists', function (Blueprint $table) {
            // No-op since we're not changing group_id anymore
        });
    }
};
