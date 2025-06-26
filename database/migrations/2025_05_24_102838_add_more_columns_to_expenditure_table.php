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
            $table->decimal('sub_total_amount', 30, 2)->default(0)->after('amount');
            $table->decimal('admin_fee_amount', 30, 2)->default(0)->after('sub_total_amount');
            $table->decimal('vat_amount', 30, 2)->default(0)->after('admin_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('sub_total_amount');
            $table->dropColumn('admin_fee_amount');
            $table->dropColumn('vat_amount');
        });
    }
};
