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
        if (!Schema::hasTable('departments')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // Drop existing foreign keys if they exist
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'departments' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('parent_id', 'bco_id', 'bo_id', 'director_id')",
            [$database]
        );
        
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `departments` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if referenced tables exist
        if (Schema::hasTable('users')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->foreign('parent_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
                
                $table->foreign('bco_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
                
                $table->foreign('bo_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
                
                $table->foreign('director_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('departments')) {
            try {
                Schema::table('departments', function (Blueprint $table) {
                    $table->dropForeign(['parent_id']);
                    $table->dropForeign(['bco_id']);
                    $table->dropForeign(['bo_id']);
                    $table->dropForeign(['director_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign keys don't exist
            }
        }
    }
};

