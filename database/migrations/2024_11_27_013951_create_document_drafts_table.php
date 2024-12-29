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
        Schema::create('document_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->unsignedBigInteger('created_by_user_id');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('current_workflow_stage_id');
            $table->foreign('current_workflow_stage_id')->references('id')->on('workflow_stages')->onDelete('cascade');
            $table->unsignedBigInteger('document_action_id');
            $table->foreign('document_action_id')->references('id')->on('document_actions')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('authorising_staff_id')->default(0);
            $table->unsignedBigInteger('document_draftable_id');
            $table->string('document_draftable_type');
            $table->string('file_path')->nullable();
            $table->string('digital_signature_path')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_drafts');
    }
};
