<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reserves')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // Drop existing foreign keys if they exist
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'reserves' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('destination_fund_id', 'expenditure_id', 'staff_id')",
            [$database]
        );
        
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `reserves` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if referenced tables exist
        if (Schema::hasTable('funds') && Schema::hasTable('expenditures') && Schema::hasTable('users')) {
            Schema::table('reserves', function (Blueprint $table) {
                $table->foreign('destination_fund_id')
                    ->references('id')
                    ->on('funds')
                    ->nullOnDelete();
                
                $table->foreign('expenditure_id')
                    ->references('id')
                    ->on('expenditures')
                    ->nullOnDelete();
                
                $table->foreign('staff_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reserves')) {
            try {
                Schema::table('reserves', function (Blueprint $table) {
                    $table->dropForeign(['destination_fund_id']);
                    $table->dropForeign(['expenditure_id']);
                    $table->dropForeign(['staff_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign keys don't exist
            }
        }
    }
};

