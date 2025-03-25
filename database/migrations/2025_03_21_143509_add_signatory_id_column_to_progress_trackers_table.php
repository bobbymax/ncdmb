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
            $table->unsignedBigInteger('signatory_id')->default(0)->after('carder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_trackers', function (Blueprint $table) {
            $table->dropColumn('signatory_id');
        });
    }
};
