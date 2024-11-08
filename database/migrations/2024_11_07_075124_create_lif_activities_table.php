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
        Schema::create('lif_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lif_institution_service_id');
            $table->foreign('lif_institution_service_id')->references('id')->on('lif_institution_services')->onDelete('cascade');
            $table->unsignedBigInteger('broker_id');
            $table->foreign('broker_id')->references('id')->on('brokers')->onDelete('cascade');
            $table->decimal('amount', 30, 2)->default(0);
            $table->bigInteger('year')->default(0);
            $table->string('period')->nullable();
            $table->string('time_frame')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lif_activities');
    }
};
