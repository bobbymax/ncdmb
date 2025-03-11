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
            $table->bigInteger('sub_document_reference_id')->unsigned()->index()->nullable()->after('document_id');
            $table->decimal('amount', 30, 2)->default(0)->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropColumn('sub_document_reference_id');
            $table->dropColumn('amount');
        });
    }
};
