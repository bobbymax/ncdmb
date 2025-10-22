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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('signatory_staff_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('alternate_signatory_staff_id')->nullable()->after('signatory_staff_id')->constrained('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign('departments_signatory_staff_id_foreign');
            $table->dropForeign('departments_alternate_signatory_staff_id_foreign');
            $table->dropColumn('signatory_staff_id');
            $table->dropColumn('alternate_signatory_staff_id');
        });
    }
};
