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
        Schema::table('workflow_stages', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['group_id']);

            // Drop the actual column
            $table->dropColumn('group_id');
            $table->dropColumn('should_upload');
            $table->dropColumn('status');
            $table->bigInteger('fallback_stage_id')->default(0)->after('workflow_stage_category_id');
            $table->enum('category', ['staff', 'third-party', 'system'])->default('system')->after('append_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->after('id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->dropColumn('category');
            $table->boolean('should_upload')->after('append_signature');
            $table->enum('status', ['passed', 'failed', 'attend', 'stalled', 'cancelled', 'complete'])->default('passed')->after('name');
            $table->dropColumn('fallback_stage_id');
        });
    }
};
