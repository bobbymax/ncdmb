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
        if (!Schema::hasTable('users')) {
            return;
        }
        
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        // First, drop any existing foreign keys to avoid duplicates
        $existingForeignKeys = $connection->select(
            "SELECT DISTINCT CONSTRAINT_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'users' 
             AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$database]
        );
        
        // Drop existing foreign keys by constraint name
        foreach ($existingForeignKeys as $fk) {
            try {
                // Use DB::statement to drop by constraint name directly
                $connection->statement("ALTER TABLE `users` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }
        
        // Now add foreign keys if the referenced tables exist
        // Using nullOnDelete() for all to allow migrate:fresh to work properly
        // Application-level validation should enforce required relationships
        if (Schema::hasTable('grade_levels')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('grade_level_id')
                    ->references('id')
                    ->on('grade_levels')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('departments')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('locations')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('location_id')
                    ->references('id')
                    ->on('locations')
                    ->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('pages')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('default_page_id')
                    ->references('id')
                    ->on('pages')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys if they exist
        if (Schema::hasTable('users')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['grade_level_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
            
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['department_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
            
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
            
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['location_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
            
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['default_page_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
        }
    }
};

