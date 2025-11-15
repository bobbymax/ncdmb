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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('fund_id')->constrained('funds');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments');
            $table->string('title')->nullable();
            $table->string('code')->unique();
            $table->decimal('total_cost', 30, 2)->default(0);
            $table->integer('no_of_items')->default(0);
            $table->enum('status', ['pending', 'in-progress', 'review', 'attested', 'approved', 'denied'])->default('pending');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('date_of_attestation')->nullable();
            $table->dateTime('date_of_review')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
