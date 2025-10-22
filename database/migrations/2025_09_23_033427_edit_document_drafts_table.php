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
            $table->foreignId('operator_id')->after('id')->nullable()->constrained('users')->nullOnDelete();
            $table->dropColumn('sub_document_reference_id');
            $table->dropForeign('document_drafts_document_type_id_foreign');
            $table->dropColumn('document_type_id');
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
            $table->dropColumn('authorising_staff_id');
            $table->dropColumn('document_draftable_id');
            $table->dropColumn('document_draftable_type');
            $table->dropColumn('file_path');
            $table->dropColumn('digital_signature_path');
            $table->dropColumn('signature');
            $table->dropColumn('type');
            $table->dropColumn('resource_type');
            $table->dropColumn('is_signed');
            $table->enum('permission', ['r', 'rw', 'rwx'])->default('r')->after('version_number');
            $table->decimal('sub_total_amount', 30, 2)->default(0)->after('permission');
            $table->decimal('vat_amount', 30, 2)->default(0)->after('sub_total_amount');
            $table->longText('remark')->nullable()->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
            $table->bigInteger('sub_document_reference_id')
                ->unsigned()
                ->nullable()
                ->index()
                ->after('document_id');

            $table->foreignId('document_type_id')->after('sub_document_reference_id')->constrained('document_types')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->after('progress_tracker_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('authorising_staff_id')->default(0)->after('department_id');
            $table->unsignedBigInteger('document_draftable_id')->after('authorising_staff_id');
            $table->string('document_draftable_type')->after('document_draftable_id');
            $table->string('file_path')->after('document_draftable_type');
            $table->string('digital_signature_path')->after('taxable_amount');
            $table->longText('signature')->after('digital_signature_path');
            $table->enum('type', ['paper', 'attention', 'response'])->default('paper')->after('signature');
            $table->string('resource_type')->nullable()->after('type');
            $table->boolean('is_signed')->default(false)->after('type');
            $table->dropColumn('permission');
            $table->dropColumn('sub_total_amount');
            $table->dropColumn('vat_amount');
            $table->dropColumn('remark');
        });
    }
};
