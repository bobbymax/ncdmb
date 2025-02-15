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
            $table->bigInteger('fallback_to_stage_id')->default(0)->after('workflow_stage_id');
            $table->bigInteger('return_to_stage_id')->default(0)->after('fallback_to_stage_id');
            $table->bigInteger('document_type_id')->default(0)->after('return_to_stage_id');
            $table->dropColumn('date_completed');
            $table->dropColumn('status');
            $table->dropColumn('is_closed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_trackers', function (Blueprint $table) {
            $table->dropColumn('fallback_to_stage_id');
            $table->dropColumn('return_to_stage_id');
            $table->dropColumn('document_type_id');
            $table->dateTime('date_completed')->nullable()->after('order');
            $table->enum('status', ['pending', 'passed', 'stalled', 'failed'])->default('pending')->after('date_completed');
            $table->boolean('is_closed')->default(false)->after('status');
        });
    }
};
