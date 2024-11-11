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
            $table->bigInteger('parentId')->default(0);
            $table->enum('type', ['directorate', 'division', 'department', 'unit'])->default('department');
            $table->bigInteger('bco')->default(0);
            $table->bigInteger('bo')->default(0);
            $table->bigInteger('director')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
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
