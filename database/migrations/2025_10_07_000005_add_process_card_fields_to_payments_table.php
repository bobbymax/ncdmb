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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('process_card_id')->nullable()->after('expenditure_id')->constrained('process_cards')->nullOnDelete();
            $table->json('process_metadata')->nullable()->after('process_card_id');
            $table->boolean('auto_generated')->default(false)->after('process_metadata');
            $table->boolean('requires_settlement')->default(false)->after('auto_generated');
            $table->boolean('is_settled')->default(false)->after('requires_settlement');
            $table->dateTime('settled_at')->nullable()->after('is_settled');
            $table->foreignId('settled_by')->nullable()->after('settled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['process_card_id']);
            $table->dropForeign(['settled_by']);
            $table->dropColumn([
                'process_card_id',
                'process_metadata',
                'auto_generated',
                'requires_settlement',
                'is_settled',
                'settled_at',
                'settled_by'
            ]);
        });
    }
};

