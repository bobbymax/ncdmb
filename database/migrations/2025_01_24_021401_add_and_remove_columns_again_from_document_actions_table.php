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
        Schema::table('document_actions', function (Blueprint $table) {
            $table->dropColumn('workflow_stage_category_id');
            $table->enum('action_status', ['passed', 'failed', 'attend', 'appeal', 'stalled', 'cancelled', 'complete'])->default('passed')->after('button_text');
            $table->dropColumn('status');
            $table->dropColumn('process_status');
            $table->string('component')->nullable()->after('variant');
            $table->enum('mode', ['store', 'update', 'destroy'])->default('store')->after('component');
            $table->enum('state', ['conditional', 'fixed'])->default('conditional')->after('mode');
            $table->boolean('has_update')->default(false)->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_actions', function (Blueprint $table) {
            $table->bigInteger('workflow_stage_category_id')->default(0)->after('id');
            $table->dropColumn('action_status');
            $table->string('status')->nullable()->after('variant');
            $table->enum('process_status', ['next', 'stall', 'goto', 'end', 'complete'])->default('next')->after('status');
            $table->dropColumn('component');
            $table->dropColumn('mode');
            $table->dropColumn('state');
            $table->dropColumn('has_update');
        });
    }
};
