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
            $table->dropColumn('threads');
            $table->dropColumn('file_path');
            $table->boolean('is_completed')->default(false)->after('is_archived');
            $table->decimal('approved_amount', 30, 2)->default(0)->after('is_completed');
            $table->decimal('sub_total_amount', 30, 2)->default(0)->after('approved_amount');
            $table->decimal('vat_amount', 30, 2)->default(0)->after('sub_total_amount');
            $table->decimal('variation_amount', 30, 2)->default(0)->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->json('threads')->nullable()->after('pointer');
            $table->string('file_path')->nullable()->after('description');
            $table->dropColumn('is_completed');
            $table->dropColumn('approved_amount');
            $table->dropColumn('sub_total_amount');
            $table->dropColumn('vat_amount');
            $table->dropColumn('variation_amount');
        });
    }
};
