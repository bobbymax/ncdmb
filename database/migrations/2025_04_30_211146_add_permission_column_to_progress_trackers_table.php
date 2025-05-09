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
        Schema::table('progress_trackers', function (Blueprint $table) {
            $table->enum('permission', ['r', 'rw', 'rwx'])->default('r')->after('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_trackers', function (Blueprint $table) {
            $table->dropColumn('permission');
        });
    }
};
