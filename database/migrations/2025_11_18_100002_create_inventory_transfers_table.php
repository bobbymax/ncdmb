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
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('to_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('transferred_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'in-transit', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('transferred_at');
            $table->timestamp('received_at')->nullable();
            $table->text('remarks')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};

