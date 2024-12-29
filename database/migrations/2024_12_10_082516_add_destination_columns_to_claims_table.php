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
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('completion_date');
            $table->dropColumn('category');
            $table->dropColumn('authority_to_travel_path');
            $table->dropColumn('approval_memo_path');
            $table->dropColumn('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->text('purpose')->after('code');
            $table->date('start_date')->nullable()->after('purpose');
            $table->date('completion_date')->nullable()->after('start_date');
            $table->string('authority_to_travel_path')->nullable()->after('type');
            $table->string('approval_memo_path')->nullable()->after('authority_to_travel_path');
            $table->enum('category', ['residence', 'non-residence'])->default('non-residence')->after('approval_memo_path');
        });
    }
};
