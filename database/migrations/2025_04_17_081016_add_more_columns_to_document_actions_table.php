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
            $table->unsignedBigInteger('trigger_workflow_id')
                ->nullable()->after('carder_id');
            $table->boolean('is_payment')->default(false)->after('trigger_workflow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_actions', function (Blueprint $table) {
            $table->dropColumn('trigger_workflow_id');
            $table->dropColumn('is_payment');
        });
    }
};
