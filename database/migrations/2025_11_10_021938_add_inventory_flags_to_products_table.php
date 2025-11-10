<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('primary_vendor_id')
                ->nullable()
                ->after('product_brand_id')
                ->constrained('vendors')
                ->nullOnDelete();
            $table->decimal('reorder_point', 20, 4)->default(0)->after('restock_qty');
            $table->decimal('max_stock_level', 20, 4)->default(0)->after('reorder_point');
            $table->boolean('track_batches')->default(false)->after('max_stock_level');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_vendor_id');
            $table->dropColumn(['reorder_point', 'max_stock_level', 'track_batches']);
        });
    }
};
