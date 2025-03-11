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
        Schema::table('expenditures', function (Blueprint $table) {
            $table->unsignedBigInteger('document_reference_id')->nullable()->after('document_draft_id');
            $table->unsignedBigInteger('expenditureable_id')->nullable()->after('budget_year');
            $table->string('expenditureable_type')->nullable()->after('expenditureable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('document_reference_id');
            $table->dropColumn('expenditureable_id');
            $table->dropColumn('expenditureable_type');
        });
    }
};
