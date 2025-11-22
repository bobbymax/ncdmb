<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('document_drafts')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // First, drop any existing foreign keys to avoid duplicates
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'document_drafts' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('progress_tracker_id', 'carder_id')",
            [$database]
        );
        
        // Drop existing foreign keys by constraint name
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `document_drafts` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if the referenced tables exist
        if (Schema::hasTable('progress_trackers')) {
            Schema::table('document_drafts', function (Blueprint $table) {
                $table->foreign('progress_tracker_id')
                    ->references('id')
                    ->on('progress_trackers')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('carders')) {
            Schema::table('document_drafts', function (Blueprint $table) {
                $table->foreign('carder_id')
                    ->references('id')
                    ->on('carders')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('document_drafts')) {
            try {
                Schema::table('document_drafts', function (Blueprint $table) {
                    $table->dropForeign(['progress_tracker_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('document_drafts', function (Blueprint $table) {
                    $table->dropForeign(['carder_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
    }
};

