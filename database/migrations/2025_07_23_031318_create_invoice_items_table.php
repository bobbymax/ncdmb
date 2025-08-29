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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->text('description');
            $table->bigInteger('qty')->default(0);
            $table->decimal('unit_price', 30, 2)->default(0);
            $table->decimal('total_amount', 30, 2)->default(0);
            $table->enum('status', ['quoted', 'revised', 'delivered'])->default('quoted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
