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
        Schema::create('workflow_workflow_stage', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('workflow_id')
                ->constrained('workflows')
                ->onDelete('cascade');

            $table->foreignId('workflow_stage_id')
                ->constrained('workflow_stages')
                ->onDelete('cascade');

            $table->unique(['workflow_id', 'workflow_stage_id']);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_workflow_stage');
    }
};
