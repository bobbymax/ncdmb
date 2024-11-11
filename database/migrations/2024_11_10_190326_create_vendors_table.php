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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('name');
            $table->string('representative_name')->nullable();
            $table->string('authorising_representative')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('ncec_no')->unique()->nullable();
            $table->string('reg_no')->unique()->nullable();
            $table->string('tin_number')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->unique()->nullable();
            $table->string('bank_name')->nullable();
            $table->string('payment_code')->unique()->nullable();
            $table->string('website')->unique()->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
