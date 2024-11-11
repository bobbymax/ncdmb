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
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('fund_id');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->bigInteger('batch_id')->default(0);
            $table->bigInteger('vendor_id')->default(0);
            $table->bigInteger('staff_id')->default(0);
            $table->bigInteger('claim_id')->default(0);
            $table->bigInteger('project_milestone_id')->default(0);
            $table->string('code')->unique();
            $table->string('beneficiary_name');
            $table->longText('payment_description');
            $table->text('additional_info')->nullable();
            $table->decimal('total_amount_raised', 30, 2)->default(0);
            $table->decimal('total_approved_amount', 30, 2)->default(0);
            $table->enum('type', ['staff-payment', 'third-party-payment'])->default('staff-payment');
            $table->enum('flag', ['debit', 'credit'])->default('debit');
            $table->enum('payment_category', ['staff-claim', 'touring-advance', 'project', 'mandate', 'milestone', 'other'])->default('staff-claim');
            $table->enum('stage', ['raised', 'batched', 'dispatched', 'budget-office', 'treasury', 'audit', 'posting'])->default('raised');
            $table->enum('status', ['pending', 'cleared', 'queried', 'paid', 'reversed', 'refunded'])->default('pending');
            $table->bigInteger('budget_year')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};
