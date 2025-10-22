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
        Schema::table('signatories', function (Blueprint $table) {
            $table->foreignId('workflow_stage_id')->nullable()->after('document_category_id')->constrained('workflow_stages')->nullOnDelete();
            $table->string('identifier')->nullable()->after('user_id');
            $table->enum('flow_type', ['from', 'through', 'to'])->default('from')->after('type');
            $table->boolean('should_sign')->default(false)->after('flow_type');
            $table->foreignId('carder_id')->nullable()->after('department_id')->constrained('carders')->nullOnDelete();
            $table->json('actions')->nullable()->after('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropForeign('signatories_workflow_stage_id_foreign');
            $table->dropColumn('workflow_stage_id');
            $table->dropColumn('identifier');
            $table->dropColumn('flow_type');
            $table->dropColumn('should_sign');
            $table->dropForeign('signatories_carder_id_foreign');
            $table->dropColumn('carder_id');
            $table->dropColumn('actions');
        });
    }
};
