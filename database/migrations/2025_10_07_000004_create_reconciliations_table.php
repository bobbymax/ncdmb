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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('funds')->nullOnDelete();
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->nullOnDelete();
            $table->enum('type', ['fund', 'ledger', 'bank', 'account'])->default('fund');
            $table->string('period');
            $table->year('fiscal_year');
            $table->decimal('system_balance', 30, 2)->default(0);
            $table->decimal('actual_balance', 30, 2)->default(0);
            $table->decimal('variance', 30, 2)->default(0);
            $table->json('discrepancies')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in-progress', 'reconciled', 'discrepancy', 'escalated'])->default('pending');
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reconciled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};

