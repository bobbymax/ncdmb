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
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->bigInteger('workflow_stage_category_id')->default(0);
            $table->bigInteger('assistant_group_id')->default(0);
            $table->bigInteger('department_id')->default(0);
            $table->bigInteger('fallback_stage_id')->default(0);
            $table->string('name');
            $table->boolean('alert_recipients')->default(false);
            $table->boolean('supporting_documents_verified')->default(false);
            $table->enum('flag', ['passed', 'failed', 'stalled'])->default('passed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_stages');
    }
};
