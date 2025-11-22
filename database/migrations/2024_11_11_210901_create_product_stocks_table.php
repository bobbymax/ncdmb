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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            // Foreign key column (constraint added in separate migration after referenced table exists)
            $table->unsignedBigInteger('store_supply_id')->nullable();
            
            $table->bigInteger('opening_stock_balance')->default(0);
            $table->bigInteger('closing_stock_balance')->default(0);
            $table->boolean('out_of_stock')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Added for data integrity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
