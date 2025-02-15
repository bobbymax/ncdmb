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
        Schema::table('mailing_lists', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['group_id']);

            // Drop the actual column
            $table->dropColumn('group_id');

            // Re-add it as a normal bigInteger column
            $table->bigInteger('group_id')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mailing_lists', function (Blueprint $table) {
            // Drop the bigInteger column
            $table->dropColumn('group_id');

            $table->unsignedBigInteger('group_id')->after('id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }
};
