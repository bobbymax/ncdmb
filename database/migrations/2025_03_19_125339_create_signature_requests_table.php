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
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');
            $table->unsignedBigInteger('document_draft_id');
            $table->foreign('document_draft_id')
                ->references('id')
                ->on('document_drafts')
                ->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
    }
};
