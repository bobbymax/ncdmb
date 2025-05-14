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
        Schema::create('journal_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->enum('deductible_to', ['total', 'sub_total', 'taxable'])->default('total');
            $table->enum('type', ['debit', 'credit', 'both'])->default('debit');
            $table->boolean('auto_generate_entries')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_types');
    }
};
