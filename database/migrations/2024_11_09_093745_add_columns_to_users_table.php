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
            $table->dropColumn('name');
            $table->string('staff_no')->unique()->nullable()->after('id');
            $table->string('firstname')->after('staff_no');
            $table->string('middlename')->nullable()->after('firstname');
            $table->string('surname')->after('middlename');
            $table->bigInteger('grade_level_id')->unsigned()->after('surname');
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->after('grade_level_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('role_id')->unsigned()->after('department_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->enum('type', ['permanent', 'contract', 'adhoc', 'secondment', 'support', 'admin'])->default('permanent')->after('password');
            $table->enum('status', ['available', 'official-assignment', 'training', 'leave', 'study', 'secondment', 'other'])->default('available')->after('type');
            $table->date('date_joined')->nullable()->after('email');
            $table->enum('gender', ['male', 'female'])->default('male')->after('date_joined');
            $table->text('job_title')->nullable()->after('gender');
            $table->boolean('is_admin')->default(false)->after('job_title');
            $table->boolean('blocked')->default(false)->after('is_admin');
            $table->boolean('change_password')->default(false)->after('blocked');
            $table->boolean('is_logged_in')->default(false)->after('change_password');
            $table->bigInteger('location_id')->default(0)->after('role_id');
            $table->string('avatar')->nullable()->after('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn('staff_no');
            $table->dropColumn('firstname');
            $table->dropColumn('middlename');
            $table->dropColumn('surname');
            $table->dropConstrainedForeignId('grade_level_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('type');
            $table->dropColumn('status');
            $table->dropColumn('is_admin');
            $table->dropColumn('date_joined');
            $table->dropColumn('gender');
            $table->dropColumn('job_title');
            $table->dropColumn('blocked');
            $table->dropColumn('change_password');
            $table->dropColumn('is_logged_in');
            $table->dropColumn('location_id');
            $table->dropColumn('avatar');
        });
    }
};
