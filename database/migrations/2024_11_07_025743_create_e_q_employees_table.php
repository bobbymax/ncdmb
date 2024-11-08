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
        Schema::create('e_q_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('e_q_approval_id');
            $table->foreign('e_q_approval_id')->references('id')->on('e_q_approvals')->onDelete('cascade');
            $table->bigInteger('expatriate_id')->default(0);

            $table->string('name');
            $table->string('nationality')->nullable();
            $table->string('position')->nullable();
            $table->string('category')->nullable();
            $table->bigInteger('years_experience')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('employee_nature')->nullable();
            $table->string('educational_qualifications')->nullable();
            $table->string('professional_qualifications')->nullable();
            $table->text('job_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_q_employees');
    }
};
