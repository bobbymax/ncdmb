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
            $table->bigInteger('trip_id')->default(0)->after('claim_id');
            $table->decimal('total_distance_covered', 8, 2)->default(0)->after('no_of_days');
            $table->decimal('unit_price', 30, 2)->default(0)->after('total_distance_covered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('trip_id');
            $table->dropColumn('total_distance_covered');
            $table->dropColumn('unit_price');
        });
    }
};
