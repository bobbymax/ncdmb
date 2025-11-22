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
        Schema::create('procurement_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            
            // Action Details
            $table->string('action', 100); // 'created', 'stage_changed', 'bid_opened', etc.
            
            // Polymorphic relationship
            $table->string('entity_type', 100)->nullable(); // 'BidInvitation', 'Bid', 'Contract'
            $table->unsignedBigInteger('entity_id')->nullable();
            
            // Changes
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('action');
            $table->index('created_at');
            $table->index(['entity_id', 'entity_type']); // Index for polymorphic relationship
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_audit_trails');
    }
};
