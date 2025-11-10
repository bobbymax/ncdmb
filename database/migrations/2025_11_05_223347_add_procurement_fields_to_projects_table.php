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
        Schema::table('projects', function (Blueprint $table) {
            // Procurement Details
            $table->enum('procurement_method', [
                'open_competitive', 'selective', 'rfq', 
                'direct', 'emergency', 'framework'
            ])->nullable()->after('lifecycle_stage');
            
            $table->string('procurement_reference', 100)->nullable()->after('code');
            
            $table->enum('procurement_type', [
                'goods', 'works', 'services', 'consultancy'
            ])->nullable()->after('procurement_method');
            
            $table->text('method_justification')->nullable()->after('procurement_type');
            
            // BPP Compliance
            $table->boolean('requires_bpp_clearance')->default(false)->after('method_justification');
            $table->string('bpp_no_objection_invite', 100)->nullable();
            $table->string('bpp_no_objection_award', 100)->nullable();
            $table->date('bpp_invite_date')->nullable();
            $table->date('bpp_award_date')->nullable();
            
            // Publication
            $table->timestamp('advertised_at')->nullable();
            $table->string('advertisement_reference', 100)->nullable();
            
            $table->index('procurement_method');
            $table->index('procurement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['procurement_method']);
            $table->dropIndex(['procurement_type']);
            
            $table->dropColumn([
                'procurement_method',
                'procurement_reference',
                'procurement_type',
                'method_justification',
                'requires_bpp_clearance',
                'bpp_no_objection_invite',
                'bpp_no_objection_award',
                'bpp_invite_date',
                'bpp_award_date',
                'advertised_at',
                'advertisement_reference',
            ]);
        });
    }
};
