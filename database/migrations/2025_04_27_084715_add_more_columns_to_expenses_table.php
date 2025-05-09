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
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('variation', 30, 2)->default(0)->after('unit_price');
            $table->enum('variation_type', ['add', 'subtract'])->default('subtract')->after('variation');
            $table->text('remark')->nullable()->after('variation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('variation');
            $table->dropColumn('variation_type');
            $table->dropColumn('remark');
        });
    }
};
