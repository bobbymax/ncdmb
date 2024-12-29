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
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('accommodation_type');
            $table->dropColumn('type');
            $table->bigInteger('trip_category_id')->default(0)->after('per_diem_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('accommodation_type', ['residence', 'non-residence'])->default('non-residence')->after('return_date');
            $table->enum('type', ['flight', 'road'])->default('flight')->after('accommodation_type');
            $table->dropColumn('trip_category_id');
        });
    }
};
