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
        Schema::create('mandates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('project_milestone_id');
            $table->foreign('project_milestone_id')->references('id')->on('project_milestones')->onDelete('cascade');
            
            // Foreign key column (constraint added in separate migration after referenced table exists)
            $table->unsignedBigInteger('expenditure_id')->nullable();
            
            $table->string('code')->unique();
            $table->bigInteger('no_of_itineraries')->default(0);
            $table->decimal('total_payable_amount', 30, 2)->default(0);
            $table->longText('instruction')->nullable();
            $table->longText('description')->nullable();
            $table->year('budget_year')->nullable(); // Changed from bigInteger default(0) to year nullable
            $table->enum('status', ['pending', 'raised', 'paid', 'reversed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandates');
    }
};
