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
        Schema::create('document_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_draft_id');
            $table->foreign('document_draft_id')->references('id')->on('document_drafts')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('document_action_id');
            $table->foreign('document_action_id')->references('id')->on('document_actions')->onDelete('cascade');
            $table->json('threads')->nullable();
            $table->longText('comment')->nullable();
            $table->enum('status', ['pending', 'responded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_updates');
    }
};
