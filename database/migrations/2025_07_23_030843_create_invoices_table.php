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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceable_id');
            $table->string('invoiceable_type');
            $table->string('invoice_number')->unique();
            $table->decimal('sub_total_amount', 30, 2)->default(0);
            $table->decimal('service_charge', 30, 2)->default(0);
            $table->decimal('grand_total_amount', 30, 2)->default(0);
            $table->json('meta_data')->nullable();
            $table->enum('currency', ['NGN', 'USD', 'EUR', 'GBP', 'YEN', 'NA'])->default('NGN');
            $table->enum('status', ['pending', 'fulfilled', 'partial', 'defaulted'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
