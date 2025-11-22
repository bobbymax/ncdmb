<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mandates')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // Drop existing foreign key if it exists
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'mandates' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME = 'expenditure_id'",
            [$database]
        );
        
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `mandates` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign key if referenced table exists
        if (Schema::hasTable('expenditures')) {
            Schema::table('mandates', function (Blueprint $table) {
                $table->foreign('expenditure_id')
                    ->references('id')
                    ->on('expenditures')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mandates')) {
            try {
                Schema::table('mandates', function (Blueprint $table) {
                    $table->dropForeign(['expenditure_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
    }
};

