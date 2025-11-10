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
        Schema::table('projects', function (Blueprint $table) {
            // Add program relationship
            $table->foreignId('program_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('project_programs')
                  ->nullOnDelete()
                  ->comment('Parent program - null means standalone project');
            
            // Phase identification
            $table->string('phase_name', 100)->nullable()->after('program_id')->comment('e.g., "Phase 1", "Phase 2A"');
            $table->integer('phase_order')->nullable()->after('phase_name')->comment('For ordering phases within a program');
            
            // Indexes
            $table->index('program_id');
            $table->index(['program_id', 'phase_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropIndex(['program_id', 'phase_order']);
            $table->dropIndex(['program_id']);
            $table->dropColumn(['program_id', 'phase_name', 'phase_order']);
        });
    }
};
