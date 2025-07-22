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
        Schema::table('allowances', function (Blueprint $table) {
            $table->string('payment_basis')->nullable()->after('description');
            $table->enum('payment_route', ['one-off', 'round-trip', 'computable'])->default('one-off')->after('payment_basis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowances', function (Blueprint $table) {
            $table->dropColumn('payment_basis');
            $table->dropColumn('payment_route');
        });
    }
};
