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
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - properly defined with nullOnDelete for optional relationships
            $table->foreignId('fund_id')->constrained('funds')->cascadeOnDelete();
            
            // Foreign key columns (constraints added in separate migration after referenced tables exist)
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('payment_batch_id')->nullable();
            
            // Core fields
            $table->string('code')->unique();
            $table->text('purpose'); // renamed from payment_description
            $table->text('additional_info')->nullable();
            
            // Amount fields
            $table->decimal('amount', 30, 2)->default(0); // renamed from total_approved_amount
            $table->decimal('sub_total_amount', 30, 2)->default(0);
            $table->decimal('admin_fee_amount', 30, 2)->default(0);
            $table->decimal('vat_amount', 30, 2)->default(0);
            
            // Type and status
            $table->string('type')->nullable(); // changed from enum to string
            $table->string('status')->default('pending'); // changed from enum to string
            $table->enum('expense_type', ['staff', 'third-party'])->default('staff');
            
            // Currency
            $table->enum('currency', ['NGN', 'USD', 'GBP', 'YEN', 'EUR'])->default('NGN');
            $table->decimal('cbn_current_rate', 15, 2)->default(0);
            
            // Year - changed from bigInteger default(0) to year nullable
            $table->year('budget_year')->nullable();
            
            // Polymorphic relationship
            $table->unsignedBigInteger('expenditureable_id')->nullable();
            $table->string('expenditureable_type')->nullable();
            
            // Metadata
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['expenditureable_id', 'expenditureable_type']);
            $table->index(['fund_id', 'status']);
            $table->index(['payment_batch_id', 'status']);
            $table->index(['document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};
