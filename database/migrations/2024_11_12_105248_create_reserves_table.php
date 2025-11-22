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
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('fund_id')->constrained('funds')->cascadeOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('destination_fund_id')->nullable();
            $table->unsignedBigInteger('expenditure_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            
            $table->decimal('total_reserved_amount', 30, 2)->default(0);
            $table->enum('status', ['pending', 'secured', 'released', 'reversed', 'rejected'])->default('pending');
            $table->string('approval_memo_path')->nullable();
            $table->string('approval_reversal_memo_path')->nullable();
            $table->date('date_reserved_approval_or_denial')->nullable();
            $table->boolean('fulfilled')->default(false);
            $table->boolean('secured')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->text('description')->nullable();
            
            // Polymorphic relationship
            $table->unsignedBigInteger('reservable_id');
            $table->string('reservable_type');
            
            $table->timestamps();
            $table->softDeletes(); // Added for data integrity
            
            // Index for polymorphic relationship
            $table->index(['reservable_id', 'reservable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};
