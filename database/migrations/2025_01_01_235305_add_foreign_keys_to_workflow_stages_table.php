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
        if (!Schema::hasTable('workflow_stages')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // First, drop any existing foreign keys to avoid duplicates
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'workflow_stages' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('workflow_stage_category_id', 'fallback_stage_id')",
            [$database]
        );
        
        // Drop existing foreign keys by constraint name
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `workflow_stages` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if the referenced tables exist
        if (Schema::hasTable('workflow_stage_categories')) {
            Schema::table('workflow_stages', function (Blueprint $table) {
                $table->foreign('workflow_stage_category_id')
                    ->references('id')
                    ->on('workflow_stage_categories')
                    ->nullOnDelete();
            });
        }
        
        // Self-referencing foreign key (workflow_stages references itself)
        if (Schema::hasTable('workflow_stages')) {
            Schema::table('workflow_stages', function (Blueprint $table) {
                $table->foreign('fallback_stage_id')
                    ->references('id')
                    ->on('workflow_stages')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('workflow_stages')) {
            try {
                Schema::table('workflow_stages', function (Blueprint $table) {
                    $table->dropForeign(['workflow_stage_category_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('workflow_stages', function (Blueprint $table) {
                    $table->dropForeign(['fallback_stage_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
    }
};

