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
        Schema::create('inbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('received_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('authorising_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('from_name');
            $table->string('from_email')->nullable();
            $table->string('from_phone')->nullable();
            $table->string('ref_no')->unique();
            $table->text('summary')->nullable();
            $table->json('instructions')->nullable();
            $table->json('analysis')->nullable();
            $table->dateTime('mailed_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('published_at')->nullable();

            $table->unsignedBigInteger('assignable_id')->nullable();
            $table->string('assignable_type')->nullable();

            $table->enum('security_class', ['public', 'internal', 'confidential', 'secret'])->default('public');
            $table->enum('channel', ['hand_delivery', 'post', 'email', 'courier', 'other'])->default('hand_delivery');
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->enum('status', ['pending', 'open', 'closed'])->default('pending');

            $table->boolean('ocr_available')->default(false);
            $table->tinyInteger('ocr_index_version')->default('0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbounds');
    }
};
