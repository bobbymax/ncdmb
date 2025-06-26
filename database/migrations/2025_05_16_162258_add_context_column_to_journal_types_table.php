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
        Schema::table('journal_types', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_id')->default(0)->after('id');
            $table->unsignedBigInteger('entity_id')->default(0)->after('ledger_id');
            $table->enum('context', ['tax', 'stamp', 'commission', 'holding', 'gross', 'net', 'reimbursement'])->default('gross')->after('code');
            $table->enum('benefactor', ['beneficiary', 'entity'])->default('entity')->after('context');
            $table->enum('state', ['fixed', 'optional'])->default('optional')->after('benefactor');
            $table->dropColumn('deductible_to');
            $table->enum('deductible', ['total', 'taxable', 'non-taxable'])->default('taxable')->after('state');
            $table->enum('category', ['staff', 'third-party', 'default'])->default('default')->after('deductible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_types', function (Blueprint $table) {
            $table->dropColumn('entity_id');
            $table->dropColumn('ledger_id');
            $table->dropColumn('benefactor');
            $table->dropColumn('context');
            $table->dropColumn('state');
            $table->enum('deductible_to', ['total', 'sub_total', 'taxable'])->default('total')->after('tax_rate');
            $table->dropColumn('deductible');
            $table->dropColumn('category');
        });
    }
};
