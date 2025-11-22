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
        if (!Schema::hasTable('progress_trackers')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // First, drop any existing foreign keys to avoid duplicates
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'progress_trackers' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('internal_process_id', 'carder_id', 'signatory_id', 'process_card_id')",
            [$database]
        );
        
        // Drop existing foreign keys by constraint name
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `progress_trackers` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if the referenced tables exist
        if (Schema::hasTable('internal_processes')) {
            Schema::table('progress_trackers', function (Blueprint $table) {
                $table->foreign('internal_process_id')
                    ->references('id')
                    ->on('internal_processes')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('carders')) {
            Schema::table('progress_trackers', function (Blueprint $table) {
                $table->foreign('carder_id')
                    ->references('id')
                    ->on('carders')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('signatories')) {
            Schema::table('progress_trackers', function (Blueprint $table) {
                $table->foreign('signatory_id')
                    ->references('id')
                    ->on('signatories')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('process_cards')) {
            Schema::table('progress_trackers', function (Blueprint $table) {
                $table->foreign('process_card_id')
                    ->references('id')
                    ->on('process_cards')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('progress_trackers')) {
            try {
                Schema::table('progress_trackers', function (Blueprint $table) {
                    $table->dropForeign(['internal_process_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('progress_trackers', function (Blueprint $table) {
                    $table->dropForeign(['carder_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('progress_trackers', function (Blueprint $table) {
                    $table->dropForeign(['signatory_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('progress_trackers', function (Blueprint $table) {
                    $table->dropForeign(['process_card_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
    }
};

