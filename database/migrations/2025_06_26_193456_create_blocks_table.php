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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('label')->unique();
            $table->string('data_type'); // textarea, input, event, table[column,source,collection],
            $table->string('input_type');
            $table->unsignedBigInteger('max_words')->default(0);
            $table->enum('type', ['staff', 'third-party', 'document'])->default('document');
//            $table->json('options')->nullable(); // header, column, data_type, input_type, format, source, condition
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
