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
        Schema::table('document_categories', function (Blueprint $table) {
            $table->enum('signature_type', ['none', 'flex', 'boxed', 'flushed', 'stacked'])->default('none')->after('service');
            $table->boolean('with_date')->default(false)->after('signature_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropColumn(['signature_type', 'with_date']);
        });
    }
};
