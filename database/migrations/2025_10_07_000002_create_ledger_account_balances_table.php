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
        Schema::create('ledger_account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('ledger_id')->constrained('ledgers')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('funds')->nullOnDelete();
            $table->decimal('opening_balance', 30, 2)->default(0);
            $table->decimal('total_debits', 30, 2)->default(0);
            $table->decimal('total_credits', 30, 2)->default(0);
            $table->decimal('closing_balance', 30, 2)->default(0);
            $table->string('period'); // e.g., "2025-01" for monthly, "2025-Q1" for quarterly
            $table->year('fiscal_year');
            $table->boolean('is_closed')->default(false);
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure unique balance record per account/ledger/period
            $table->unique(['chart_of_account_id', 'ledger_id', 'period', 'fiscal_year'], 'unique_account_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_account_balances');
    }
};

