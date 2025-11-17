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
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for frequently queried columns
            $table->index('department_id', 'users_department_id_index');
            $table->index('grade_level_id', 'users_grade_level_id_index');
            $table->index('role_id', 'users_role_id_index');
            $table->index('blocked', 'users_blocked_index');
            $table->index('email', 'users_email_index');
            $table->index('status', 'users_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('users_department_id_index');
            $table->dropIndex('users_grade_level_id_index');
            $table->dropIndex('users_role_id_index');
            $table->dropIndex('users_blocked_index');
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_status_index');
        });
    }
};
