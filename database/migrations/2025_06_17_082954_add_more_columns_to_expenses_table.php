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
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('cleared_amount', 30, 2)->default(0)->after('total_amount_spent');
            $table->decimal('audited_amount', 30, 2)->default(0)->after('cleared_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('cleared_amount');
            $table->dropColumn('audited_amount');
        });
    }
};
