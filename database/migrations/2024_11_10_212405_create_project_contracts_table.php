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
        Schema::create('project_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('board_project_id');
            $table->foreign('board_project_id')->references('id')->on('board_projects')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('code')->unique()->nullable();
            $table->string('contract_code')->unique()->nullable();
            $table->string('acceptance_letter')->nullable();
            $table->date('date_of_acceptance')->nullable();
            $table->decimal('total_contract_value', 30, 2)->default(0);
            $table->decimal('total_project_value', 30, 2)->default(0);
            $table->enum('nb_vendor_change', ['na', 'request', 'approved', 'rejected'])->default('na');
            $table->enum('status', ['pending', 'accepted', 'rejected', 're-called'])->default('pending');
            $table->enum('state', ['na', 'uncompleted', 'partial', 'completed'])->default('na');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_contracts');
    }
};
