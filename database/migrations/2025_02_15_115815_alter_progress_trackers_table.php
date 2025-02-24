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
            $table->dropColumn('fallback_to_stage_id');
            $table->dropColumn('return_to_stage_id');
            $table->bigInteger('group_id')->default(0)->after('workflow_stage_id');
            $table->bigInteger('department_id')->default(0)->after('group_id');
            $table->bigInteger('carder_id')->default(0)->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_trackers', function (Blueprint $table) {
            $table->dropColumn('group_id');
            $table->dropColumn('department_id');
            $table->dropColumn('carder_id');
            $table->bigInteger('fallback_to_stage_id')->default(0)->after('workflow_stage_id');
            $table->bigInteger('return_to_stage_id')->default(0)->after('fallback_to_stage_id');
        });
    }
};
