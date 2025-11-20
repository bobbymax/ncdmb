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
        Schema::create('contract_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_contract_id')->constrained('project_contracts')->cascadeOnDelete();
            $table->enum('dispute_type', [
                'payment',
                'performance',
                'variation',
                'termination',
                'quality',
                'delay',
                'other'
            ])->default('payment');
            $table->string('dispute_reference', 100)->unique();
            $table->text('description');
            $table->enum('raised_by', [
                'contractor',
                'government',
                'both'
            ])->default('contractor');
            $table->date('raised_date');
            $table->enum('status', [
                'open',
                'under_negotiation',
                'mediation',
                'arbitration',
                'litigation',
                'resolved',
                'escalated',
                'closed'
            ])->default('open');
            $table->enum('resolution_method', [
                'negotiation',
                'mediation',
                'arbitration',
                'litigation',
                'settlement',
                'other'
            ])->nullable();
            $table->date('resolved_date')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('disputed_amount', 20, 2)->nullable();
            $table->decimal('resolved_amount', 20, 2)->nullable();
            $table->foreignId('legal_counsel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('legal_advice')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['project_contract_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_disputes');
    }
};

