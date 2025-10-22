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
            $table->string('name')->nullable()->after('entity_id');
            $table->enum('kind', ['add', 'deduct', 'info'])->default('info')->after('code');
            $table->decimal('rate', 8, 2)->default(0)->after('description');
            $table->enum('rate_type', ['percent', 'fixed'])->default('percent')->after('rate');
            $table->enum('base_selector', ['GROSS', 'TAXABLE', 'NON-TAXABLE', 'CUSTOM'])->default('TAXABLE')->after('rate_type');
            $table->decimal('fixed_amount', 30, 2)->default(0)->after('base_selector');
            $table->boolean('deductible_from_taxable')->default(false)->after('fixed_amount');
            $table->bigInteger('precedence')->default(100)->after('deductible_from_taxable');
            $table->enum('rounding', ['half_up', 'bankers'])->default('half_up')->after('precedence');
            $table->boolean('is_vat')->default(false)->after('rounding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('kind');
            $table->dropColumn('rate');
            $table->dropColumn('rate_type');
            $table->dropColumn('base_selector');
            $table->dropColumn('fixed_amount');
            $table->dropColumn('deductible_from_taxable');
            $table->dropColumn('precedence');
            $table->dropColumn('rounding');
            $table->dropColumn('is_vat');
        });
    }
};
