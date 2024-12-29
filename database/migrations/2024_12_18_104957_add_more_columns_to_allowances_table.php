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
        Schema::table('allowances', function (Blueprint $table) {
            $table->bigInteger('departure_city_id')->default(0)->after('parent_id');
            $table->bigInteger('destination_city_id')->default(0)->after('departure_city_id');
            $table->string('component')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowances', function (Blueprint $table) {
            $table->dropColumn('departure_city_id');
            $table->dropColumn('destination_city_id');
            $table->dropColumn('component');
        });
    }
};
