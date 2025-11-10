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
        Schema::create('project_bid_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_bid_id')->constrained('project_bids')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users');
            
            // Evaluation Type
            $table->enum('evaluation_type', ['administrative', 'technical', 'financial', 'post_qualification']);
            $table->date('evaluation_date');
            
            // Scores
            $table->json('criteria')->nullable(); // [{criterion, max_score, awarded_score, comments}]
            $table->decimal('total_score', 5, 2)->nullable();
            $table->enum('pass_fail', ['pass', 'fail', 'conditional'])->nullable();
            
            // Comments
            $table->text('comments')->nullable();
            $table->text('recommendations')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            
            $table->timestamps();
            
            $table->index('evaluation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_bid_evaluations');
    }
};
