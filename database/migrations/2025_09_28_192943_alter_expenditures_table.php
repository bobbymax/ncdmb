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
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('document_draft_id');
            $table->dropColumn('document_reference_id');
            $table->dropColumn('expenditureable_id');
            $table->dropColumn('expenditureable_type');
            $table->dropForeign('expenditures_user_id_foreign');
            $table->dropColumn('user_id');
            $table->dropForeign('expenditures_department_id_foreign');
            $table->dropColumn('department_id');
            $table->foreignId('document_id')->nullable()->after('fund_id')->constrained('documents')->nullOnDelete();
            $table->foreignId('payment_batch_id')->nullable()->after('document_id')->constrained('payment_batches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropForeign('expenditures_payment_batch_id_foreign');
            $table->dropColumn('payment_batch_id');
            $table->dropForeign('expenditures_document_id_foreign');
            $table->dropColumn('document_id');
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('expenditureable_id')->nullable()->after('expense_type');
            $table->string('expenditureable_type')->nullable()->after('expense_type');
            $table->unsignedBigInteger('document_reference_id')->nullable()->after('department_id');
            $table->bigInteger('document_draft_id')->default(0)->after('fund_id');
        });
    }
};
