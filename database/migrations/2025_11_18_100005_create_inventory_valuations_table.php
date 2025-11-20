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
        Schema::create('inventory_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_measurement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->enum('valuation_method', ['fifo', 'lifo', 'weighted_average', 'specific_identification'])->default('weighted_average');
            $table->decimal('unit_cost', 20, 4);
            $table->decimal('quantity_on_hand', 20, 4);
            $table->decimal('total_value', 20, 2);
            $table->timestamp('valued_at');
            $table->foreignId('valued_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'location_id', 'valued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_valuations');
    }
};

