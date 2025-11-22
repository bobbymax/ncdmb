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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Personal information
            $table->string('staff_no')->unique()->nullable();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('surname');
            
            // Authentication
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('refresh_token')->nullable();
            
            // Two-factor authentication
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            // Made nullable to allow migrate:fresh to work properly
            $table->unsignedBigInteger('grade_level_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('default_page_id')->nullable();
            
            // Employment details
            $table->enum('type', ['permanent', 'contract', 'adhoc', 'secondment', 'support', 'admin'])->default('permanent');
            $table->enum('status', ['available', 'official-assignment', 'training', 'leave', 'study', 'secondment', 'other'])->default('available');
            $table->date('date_joined')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->text('job_title')->nullable();
            
            // Flags
            $table->boolean('is_admin')->default(false);
            $table->boolean('blocked')->default(false);
            $table->boolean('change_password')->default(false);
            $table->boolean('is_logged_in')->default(false);
            
            // Avatar
            $table->string('avatar')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Note: Indexes are added in a separate migration (2025_11_17_061237_add_indexes_to_users_table_for_performance.php)
            // to avoid duplicate index errors
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
