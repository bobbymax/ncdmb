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
        Schema::create('document_actionables', function (Blueprint $table) {
            $table->unsignedBigInteger('document_action_id');
            $table->unsignedBigInteger('document_actionable_id');
            $table->string('document_actionable_type');
            
            // Indexes for polymorphic relationship
            $table->index(['document_actionable_id', 'document_actionable_type'], 'doc_actionables_poly_idx');
            $table->index('document_action_id', 'doc_actionables_action_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_actionables');
    }
};
