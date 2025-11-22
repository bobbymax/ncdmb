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
        Schema::create('trial_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('period'); // "2025-01" (monthly) or "2025-Q1" (quarterly)
            $table->year('fiscal_year');
            $table->decimal('total_debits', 30, 2)->default(0);
            $table->decimal('total_credits', 30, 2)->default(0);
            $table->decimal('variance', 30, 2)->default(0); // Should always be 0 if balanced
            $table->json('account_balances')->nullable(); // Summary by account type (asset, liability, etc.)
            $table->json('ledger_summary')->nullable(); // Summary by ledger
            $table->boolean('is_balanced')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique trial balance per department/period
            $table->unique(['department_id', 'period', 'fiscal_year'], 'unique_trial_balance_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_balances');
    }
};

