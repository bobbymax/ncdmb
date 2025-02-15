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
            $table->dropColumn('document_type_id');
            $table->dropColumn('document_category_id');
            $table->dropColumn('assistant_group_id');
            $table->dropColumn('fallback_stage_id');
            $table->dropColumn('alert_recipients');
            $table->enum('status', ['passed', 'failed', 'attend', 'appeal', 'stalled', 'cancelled', 'complete'])->default('passed')->after('name');
            $table->boolean('can_appeal')->default(false)->after('status');
            $table->boolean('append_signature')->default(false)->after('can_appeal');
            $table->boolean('should_upload')->default(false)->after('append_signature');
            $table->dropColumn('supporting_documents_verified');
            $table->dropColumn('flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->integer('document_type_id')->default(0)->after('workflow_stage_category_id');
            $table->integer('document_category_id')->default(0)->after('document_type_id');
            $table->integer('assistant_group_id')->default(0)->after('document_category_id');
            $table->integer('fallback_stage_id')->default(0)->after('department_id');
            $table->boolean('alert_recipients')->default(false)->after('name');
            $table->dropColumn('status');
            $table->dropColumn('can_appeal');
            $table->dropColumn('append_signature');
            $table->dropColumn('should_upload');
            $table->boolean('supporting_documents_verified')->default(false)->after('alert_recipients');
            $table->enum('flag', ['passed', 'failed', 'stalled'])->default('passed')->after('supporting_documents_verified');
        });
    }
};
