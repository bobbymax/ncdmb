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
        Schema::create('project_bid_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            
            // Invitation Details
            $table->string('invitation_reference', 100)->unique();
            $table->string('title', 500);
            $table->text('description')->nullable();
            
            // Technical Specifications
            $table->text('technical_specifications')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            // Document Requirements
            $table->json('required_documents')->nullable(); // [{name, description, mandatory}]
            $table->json('eligibility_criteria')->nullable();
            
            // Financial
            $table->boolean('bid_security_required')->default(true);
            $table->decimal('bid_security_amount', 20, 2)->nullable();
            $table->integer('bid_security_validity_days')->default(90);
            $table->decimal('estimated_contract_value', 20, 2)->nullable();
            
            // Timeline
            $table->date('advertisement_date')->nullable();
            $table->dateTime('pre_bid_meeting_date')->nullable();
            $table->string('pre_bid_meeting_location', 500)->nullable();
            $table->dateTime('submission_deadline');
            $table->integer('bid_validity_days')->default(90);
            $table->dateTime('opening_date');
            $table->string('opening_location', 500)->nullable();
            
            // Evaluation Criteria
            $table->json('evaluation_criteria')->nullable(); // [{criterion, weight, scoring_method}]
            $table->decimal('technical_weight', 5, 2)->default(70.00);
            $table->decimal('financial_weight', 5, 2)->default(30.00);
            
            // Publication
            $table->json('published_newspapers')->nullable(); // [{name, date, page}]
            $table->boolean('published_bpp_portal')->default(false);
            
            // Uploads
            $table->string('tender_document_url', 500)->nullable();
            $table->string('bill_of_quantities_url', 500)->nullable();
            
            // Status
            $table->enum('status', ['draft', 'published', 'closed', 'cancelled'])->default('draft');
            
            $table->timestamps();
            
            $table->index('submission_deadline');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_bid_invitations');
    }
};
