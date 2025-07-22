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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('threshold_id')->constrained('thresholds');
            $table->foreignId('project_category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->string('code')->unique()->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->decimal('total_proposed_amount', 30, 2)->default(0);
            $table->decimal('total_approved_amount', 30, 2)->default(0);
            $table->decimal('variation_amount', 30, 2)->default(0);
            $table->decimal('sub_total_amount', 30, 2)->default(0);
            $table->decimal('vat_amount', 30, 2)->default(0);
            $table->decimal('markup_amount', 30, 2)->default(0);
            $table->integer('service_charge_percentage')->default(0);
            $table->date('proposed_start_date')->nullable();
            $table->date('approved_start_date')->nullable();
            $table->date('approved_end_date')->nullable();
            $table->date('proposed_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('type', ['staff', 'third-party'])->default('third-party');
            $table->enum('status', ['pending', 'registered', 'approved', 'denied', 'kiv', 'discussed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
