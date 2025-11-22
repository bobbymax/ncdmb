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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - properly defined
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('sponsoring_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('authorising_staff_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('document_category_id')->nullable();
            $table->unsignedBigInteger('departure_city_id')->nullable();
            $table->unsignedBigInteger('destination_city_id')->nullable();
            $table->unsignedBigInteger('airport_id')->nullable();
            
            // Core fields
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Amount fields
            $table->decimal('total_amount_spent', 30, 2)->default(0);
            $table->decimal('total_amount_approved', 30, 2)->default(0);
            $table->decimal('total_amount_retired', 30, 2)->default(0);
            
            // Type and status
            $table->enum('type', ['claim', 'retirement'])->default('claim');
            $table->string('status')->nullable(); // changed from enum to string
            
            // Travel details
            $table->enum('resident_type', ['resident', 'non-resident'])->default('non-resident');
            $table->unsignedBigInteger('distance')->default(0);
            $table->enum('mode', ['flight', 'road', 'other'])->default('flight');
            $table->enum('route', ['one-way', 'return', 'multiple'])->default('return');
            
            // Signatures
            $table->longText('claimant_signature')->nullable();
            $table->longText('approval_signature')->nullable();
            
            // Metadata
            $table->boolean('retired')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['document_category_id']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
