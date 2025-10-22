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
        Schema::create('document_panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->string('name');
            $table->string('label')->unique();
            $table->string('icon');
            $table->string('component_path');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_editor_only')->default(false);
            $table->boolean('is_view_only')->default(false);
            $table->enum('visibility_mode', ['both', 'editor', 'preview'])->default('preview');
            $table->boolean('is_global')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_panels');
    }
};
