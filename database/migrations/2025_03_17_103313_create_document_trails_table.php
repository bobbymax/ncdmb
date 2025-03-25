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
        Schema::create('document_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('document_draft_id');
            $table->unsignedBigInteger('document_action_id');
            $table->foreign('document_action_id')->references('id')->on('document_actions')->onDelete('cascade');
            $table->longText('reason')->nullable();
            $table->unsignedBigInteger('document_trailable_id');
            $table->string('document_trailable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_trails');
    }
};
