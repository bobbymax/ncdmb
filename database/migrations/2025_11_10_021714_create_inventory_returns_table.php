<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_supply_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['to_supplier', 'from_project', 'internal']);
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->timestamp('returned_at');
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_returns');
    }
};
