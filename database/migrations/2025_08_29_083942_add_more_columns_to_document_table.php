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
            $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->json('meta_data')->nullable()->after('contents');
            $table->json('uploaded_requirements')->nullable()->after('meta_data');
            $table->json('preferences')->nullable()->after('uploaded_requirements');
            $table->json('watchers')->nullable()->after('preferences');
            $table->string('pointer')->nullable()->after('watchers');
            $table->json('threads')->nullable()->after('pointer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropColumn('meta_data');
            $table->dropColumn('uploaded_requirements');
            $table->dropColumn('preferences');
            $table->dropColumn('watchers');
            $table->dropColumn('threads');
            $table->dropColumn('pointer');
        });
    }
};
