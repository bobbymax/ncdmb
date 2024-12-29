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
        Schema::table('document_drafts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['document_action_id']);

            // Then drop the column
            $table->dropColumn('document_action_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            // Add the foreign key column
            $table->unsignedBigInteger('document_action_id')->after('current_workflow_stage_id');

            // Add the foreign key constraint
            $table->foreign('document_action_id')
                ->references('id')
                ->on('document_actions')
                ->onDelete('cascade');
        });
    }
};
