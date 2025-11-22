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
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            // Note: email and staff_no already have unique() constraints which create indexes automatically
            $table->index('department_id', 'users_department_id_index');
            $table->index('grade_level_id', 'users_grade_level_id_index');
            $table->index('role_id', 'users_role_id_index');
            $table->index('blocked', 'users_blocked_index');
            $table->index('status', 'users_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if foreign keys exist - if they do, we can't drop the indexes
        // because MySQL requires indexes for foreign key constraints
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $hasForeignKeys = $connection->select(
            "SELECT COUNT(*) as count
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'users' 
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND COLUMN_NAME IN ('department_id', 'grade_level_id', 'role_id')",
            [$database]
        );
        
        $hasFk = $hasForeignKeys[0]->count > 0;
        
        Schema::table('users', function (Blueprint $table) use ($hasFk) {
            // Only drop indexes that are not needed by foreign key constraints
            // Note: email and staff_no indexes are created by unique() constraints and should not be dropped here
            if (!$hasFk) {
                // Only drop indexes if foreign keys don't exist
                $table->dropIndex('users_department_id_index');
                $table->dropIndex('users_grade_level_id_index');
                $table->dropIndex('users_role_id_index');
            }
            // These indexes are safe to drop as they're not used by foreign keys
            $table->dropIndex('users_blocked_index');
            $table->dropIndex('users_status_index');
        });
    }
};
