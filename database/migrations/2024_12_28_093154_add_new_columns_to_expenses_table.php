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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('identifier')->unique()->nullable()->after('id');
            $table->string('description')->nullable()->after('no_of_days');
            $table->bigInteger('parent_id')->default(0)->after('trip_id');
            $table->bigInteger('allowance_id')->default(0)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('identifier');
            $table->dropColumn('description');
            $table->dropColumn('parent_id');
            $table->dropColumn('allowance_id');
        });
    }
};
