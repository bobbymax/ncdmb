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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('payment_batch_id')->nullable()->constrained('payment_batches')->nullOnDelete();
            $table->foreignId('document_draft_id')->nullable()->constrained('document_drafts')->nullOnDelete();
            $table->foreignId('expenditure_id')->nullable()->constrained('expenditures')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('narration')->nullable();
            $table->decimal('total_approved_amount', 30, 2)->default(0);
            $table->decimal('total_amount_paid', 30, 2)->default(0);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('resource_type')->nullable();
            
            // Index for polymorphic relationship
            $table->index(['resource_id', 'resource_type']);
            
            $table->date('period')->nullable();
            $table->year('budget_year')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->enum('type', ['staff', 'third-party'])->default('staff');
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
