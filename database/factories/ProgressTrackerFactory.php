<?php

namespace Database\Factories;

use App\Models\DocumentType;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProgressTracker>
 */
class ProgressTrackerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'workflow_stage_id' => WorkflowStage::factory(),
            'document_type_id' => DocumentType::factory(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
