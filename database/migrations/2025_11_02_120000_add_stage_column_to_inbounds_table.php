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
        Schema::table('inbounds', function (Blueprint $table) {
            // Add stage column for tracking AI analysis progress
            $table->enum('stage', [
                'pending',      // Initial state, not yet analyzed
                'queueing',     // Job is being queued
                'extracting',   // Extracting text from PDF
                'analyzing',    // Sending to AI for analysis
                'completing',   // Finalizing results
                'completed',    // Analysis completed successfully
                'failed'        // Analysis failed
            ])->default('pending')->after('status');
            
            // Add indexes for better query performance
            $table->index('stage');
            $table->index(['status', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbounds', function (Blueprint $table) {
            $table->dropIndex(['inbounds_stage_index']);
            $table->dropIndex(['inbounds_status_stage_index']);
            $table->dropColumn('stage');
        });
    }
};

