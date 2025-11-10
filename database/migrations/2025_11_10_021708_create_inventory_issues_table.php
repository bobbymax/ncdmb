<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issued_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('from_location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->timestamp('issued_at');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_issues');
    }
};
