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
        Schema::table('signatories', function (Blueprint $table) {
            $table->foreignId('document_category_id')->nullable()->after('department_id')->constrained('document_categories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('document_category_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropForeign(['document_category_id']);
            $table->dropColumn('document_category_id');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
