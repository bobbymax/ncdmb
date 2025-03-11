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
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('total_expected_spent_amount');
            $table->dropColumn('total_actual_spent_amount');
            $table->dropColumn('total_booked_balance');
            $table->dropColumn('total_actual_balance');
            $table->dropColumn('total_reserved_amount');
            $table->enum('type', ['capital', 'recurrent', 'personnel'])->default('capital')->after('total_approved_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->decimal('total_expected_spent_amount', 30, 2)->default(0)->after('total_approved_amount');
            $table->decimal('total_actual_spent_amount', 30, 2)->default(0)->after('total_expected_spent_amount');
            $table->decimal('total_booked_balance', 30, 2)->default(0)->after('total_actual_spent_amount');
            $table->decimal('total_actual_balance', 30, 2)->default(0)->after('total_booked_balance');
            $table->decimal('total_reserved_amount', 30, 2)->default(0)->after('total_actual_balance');
            $table->dropColumn('type');
        });
    }
};
