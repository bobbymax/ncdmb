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
        // Adding Soft Deletes to 'documents' table
        Schema::table('documents', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Adding Soft Deletes to 'expenditures' table
        Schema::table('expenditures', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('document_drafts', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Removing Soft Deletes from 'expenditures' table
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('document_drafts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Removing Soft Deletes from 'expenditures' table
        Schema::table('claims', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
