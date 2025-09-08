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
        Schema::table('claims', function (Blueprint $table) {
            $table->foreignId('departure_city_id')->nullable()->after('document_category_id')->constrained('cities')->nullOnDelete();
            $table->foreignId('destination_city_id')->nullable()->after('departure_city_id')->constrained('cities')->nullOnDelete();
            $table->foreignId('airport_id')->nullable()->after('destination_city_id')->constrained('cities')->nullOnDelete();

            $table->enum('resident_type', ['resident', 'non-resident'])->default('non-resident')->after('type');
            $table->unsignedBigInteger('distance')->default(0)->after('resident_type');
            $table->enum('mode', ['flight', 'road', 'other'])->default('flight')->after('distance');
            $table->enum('route', ['one-way', 'return', 'multiple'])->default('return')->after('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['departure_city_id']);
            $table->dropForeign(['destination_city_id']);
            $table->dropForeign(['airport_id']);
            $table->dropColumn(['departure_city_id', 'destination_city_id', 'airport_id', 'resident_type', 'mode', 'route', 'distance']);

        });
    }
};
