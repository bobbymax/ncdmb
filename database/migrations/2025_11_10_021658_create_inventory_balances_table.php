<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_measurement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->decimal('on_hand', 20, 4)->default(0);
            $table->decimal('reserved', 20, 4)->default(0);
            $table->decimal('available', 20, 4)->default(0);
            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'product_measurement_id', 'location_id'], 'inventory_balance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
