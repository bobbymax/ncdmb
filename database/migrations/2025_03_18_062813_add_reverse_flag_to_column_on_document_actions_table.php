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
        Schema::table('document_actions', function (Blueprint $table) {
            $table->enum('action_status', ['passed', 'failed', 'attend', 'appeal', 'stalled', 'cancelled', 'reversed', 'complete'])->default('passed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_actions', function (Blueprint $table) {
            $table->enum('action_status', ['passed', 'failed', 'attend', 'appeal', 'stalled', 'cancelled', 'complete'])->default('passed')->change();
        });
    }
};
