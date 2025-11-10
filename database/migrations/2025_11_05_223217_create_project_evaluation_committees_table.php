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
        Schema::create('project_evaluation_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            
            // Committee Details
            $table->string('committee_name', 255);
            $table->enum('committee_type', ['tender_board', 'technical', 'financial', 'opening']);
            $table->foreignId('chairman_id')->constrained('users');
            
            // Members (JSON array of user_ids with roles)
            $table->json('members')->nullable(); // [{user_id, role: 'chairman'|'secretary'|'member'}]
            
            // Status
            $table->enum('status', ['active', 'dissolved'])->default('active');
            $table->timestamp('formed_at')->nullable();
            $table->timestamp('dissolved_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_evaluation_committees');
    }
};
