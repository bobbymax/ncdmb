<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Group;
use App\Models\ProgressTracker;
use App\Models\User;
use App\Models\WorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentDraft>
 */
class DocumentDraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'group_id' => Group::factory(),
            'department_id' => Department::factory(),
            'document_type_id' => DocumentType::factory(),
            'progress_tracker_id' => ProgressTracker::factory(),
            'created_by_user_id' => User::factory(),
            'current_workflow_stage_id' => WorkflowStage::factory(),
            'document_draftable_id' => Document::factory(),
            'document_draftable_type' => Document::class,
        ];
    }
}
