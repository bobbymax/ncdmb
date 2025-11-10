<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_supplies', function (Blueprint $table) {
            $table->foreignId('inventory_location_id')
                ->nullable()
                ->after('project_contract_id')
                ->constrained('inventory_locations')
                ->nullOnDelete();
            $table->string('delivery_reference')->nullable()->after('inventory_location_id');
            $table->timestamp('received_at')->nullable()->after('delivery_date');
            $table->json('inspection_meta')->nullable()->after('period_published');
        });
    }

    public function down(): void
    {
        Schema::table('store_supplies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_location_id');
            $table->dropColumn(['delivery_reference', 'received_at', 'inspection_meta']);
        });
    }
};
