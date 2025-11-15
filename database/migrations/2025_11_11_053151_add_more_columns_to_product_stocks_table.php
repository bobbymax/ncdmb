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
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->date('end_of_life')->nullable()->after('out_of_stock');
            $table->enum('stock_in', ['purchase', 'production', 'transfer', 'allocation'])->default('purchase')->after('end_of_life');
            $table->enum('stock_out', ['sales', 'consumption', 'waste', 'distribution'])->default('sales')->after('stock_in');
            $table->boolean('is_active')->default(false)->after('stock_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropColumn('end_of_life');
            $table->dropColumn('stock_in');
            $table->dropColumn('stock_out');
            $table->dropColumn('is_active');
        });
    }
};
