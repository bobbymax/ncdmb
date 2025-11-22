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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->unique();
            $table->string('abv')->unique();
            $table->string('department_payment_code')->unique()->nullable();
            
            // Foreign key columns (constraints added in separate migration after users table exists)
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('bco_id')->nullable();
            $table->unsignedBigInteger('bo_id')->nullable();
            $table->unsignedBigInteger('director_id')->nullable();
            
            $table->enum('type', ['directorate', 'division', 'department', 'unit'])->default('department');
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
