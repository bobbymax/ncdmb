<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use App\Models\ProgressTracker;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'workflow_id' => Workflow::factory(),
            'progress_tracker_id' => ProgressTracker::factory(),
            'document_category_id' => DocumentCategory::factory(),
            'document_type_id' => DocumentType::factory(),
            'documentable_id' => User::factory(),
            'documentable_type' => User::class,
            'ref' => $this->faker->uuid,
            'description' => $this->faker->paragraph,
        ];
    }
}
