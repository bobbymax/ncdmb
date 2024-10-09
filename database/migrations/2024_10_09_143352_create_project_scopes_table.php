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
        Schema::create('project_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('contractor_id');

            $table->longText('scope')->nullable();
            $table->longText('nc_scope')->nullable();
            $table->longText('non_nc_scope')->nullable();
            $table->decimal('contract_value', 30, 2)->default(0);
            $table->decimal('nc_value', 30, 2)->default(0);

            $table->bigInteger('no_of_nigerians')->default(0);
            $table->bigInteger('no_of_expatriates')->default(0);

            $table->bigInteger('ngn_man_hrs')->default(0);
            $table->bigInteger('expatriates_man_hrs')->default(0);

            $table->date('expected_start_date')->nullable();
            $table->date('actual_start_date')->nullable();

            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('schedule_id')
                ->references('id')
                ->on('schedules')
                ->onDelete('cascade');

            $table->foreign('contractor_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_scopes');
    }
};
