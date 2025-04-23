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
        Schema::create('document_hierarchy', function (Blueprint $table) {
            $table->unsignedBigInteger('document_id');         // The current/child document
            $table->unsignedBigInteger('linked_document_id');   // The parent/originating document
            $table->string('relationship_type')->nullable();    // e.g. 'batch_source', 'voucher_of_batch'
            $table->timestamps();

            $table->primary(['document_id', 'linked_document_id']);

            $table->foreign('document_id')
                ->references('id')->on('documents')
                ->onDelete('cascade');

            $table->foreign('linked_document_id')
                ->references('id')->on('documents')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_hierarchy');
    }
};
