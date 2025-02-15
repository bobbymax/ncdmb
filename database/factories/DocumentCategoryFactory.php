<?php

namespace Database\Factories;

use App\Models\DocumentType;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentCategory>
 */
class DocumentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_type_id' => DocumentType::factory(),
            'workflow_id' => Workflow::factory(),
            'name' => $this->faker->name(),
            'label' => Str::slug($this->faker->name()),
            'description' => $this->faker->text(),
        ];
    }
}
