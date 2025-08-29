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
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable()->after('document_action_id')->constrained('funds')->nullOnDelete();
            $table->foreignId('threshold_id')->nullable()->after('fund_id')->constrained('thresholds')->nullOnDelete();
            $table->json('config')->nullable()->after('threshold_id');
            $table->json('contents')->nullable()->after('config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['fund_id']);
            $table->dropColumn('fund_id');
            $table->dropForeign(['threshold_id']);
            $table->dropColumn('threshold_id');
            $table->dropColumn('config');
            $table->dropColumn('contents');
        });
    }
};
