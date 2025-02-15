<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkflowStage>
 */
class WorkflowStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'department_id' => Department::factory(),
            'name' => $this->faker->name(),
        ];
    }
}
