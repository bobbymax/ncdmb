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
        Schema::create('document_actions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workflow_stage_category_id')->default(0);
            $table->bigInteger('carder_id')->default(0);
            $table->string('component')->nullable();
            $table->enum('mode', ['store', 'update', 'destroy'])->default('store');
            $table->enum('state', ['conditional', 'fixed'])->default('conditional');
            $table->boolean('has_update')->default(false);
            $table->string('name');
            $table->string('label')->unique();
            $table->string('button_text')->nullable();
            $table->string('icon')->nullable();
            $table->enum('variant', ['primary', 'info', 'success', 'warning', 'danger', 'dark'])->default('primary');
            $table->enum('category', ['signature', 'comment', 'template', 'request', 'resource', 'upload'])->default('comment');
            $table->enum('action_status', [
                'passed', 'failed', 'attend', 'appeal', 'escalate',
                'processing', 'stalled', 'cancelled', 'reversed', 'complete'
            ])->default('passed');
            $table->text('description')->nullable();
            $table->boolean('is_resource')->default(false);
            $table->string('draft_status')->nullable();
            $table->enum('resource_type', ['searchable', 'classified', 'private', 'archived', 'computed', 'generated', 'report', 'other'])->default('searchable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_actions');
    }
};
