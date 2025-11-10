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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_measurement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('project_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_supply_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_return_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_adjustment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', [
                'receipt',
                'issue',
                'transfer_out',
                'transfer_in',
                'return',
                'adjustment_plus',
                'adjustment_minus',
                'reservation',
            ]);
            $table->decimal('quantity', 20, 4);
            $table->decimal('unit_cost', 20, 4)->nullable();
            $table->decimal('value', 20, 4)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('transacted_at');
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
