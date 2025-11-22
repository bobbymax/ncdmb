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
        if (!Schema::hasTable('claims')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // First, drop any existing foreign keys to avoid duplicates
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'claims' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('document_category_id', 'departure_city_id', 'destination_city_id', 'airport_id')",
            [$database]
        );
        
        // Drop existing foreign keys by constraint name
        foreach ($existingForeignKeys as $fk) {
            try {
                $connection->statement("ALTER TABLE `claims` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Add foreign keys if the referenced tables exist
        if (Schema::hasTable('document_categories')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->foreign('document_category_id')
                    ->references('id')
                    ->on('document_categories')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('cities')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->foreign('departure_city_id')
                    ->references('id')
                    ->on('cities')
                    ->nullOnDelete();
                
                $table->foreign('destination_city_id')
                    ->references('id')
                    ->on('cities')
                    ->nullOnDelete();
                
                $table->foreign('airport_id')
                    ->references('id')
                    ->on('cities')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('claims')) {
            try {
                Schema::table('claims', function (Blueprint $table) {
                    $table->dropForeign(['document_category_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                Schema::table('claims', function (Blueprint $table) {
                    $table->dropForeign(['departure_city_id']);
                    $table->dropForeign(['destination_city_id']);
                    $table->dropForeign(['airport_id']);
                });
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
    }
};

