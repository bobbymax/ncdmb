<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Document;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DraftFactory extends Factory
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
        ];
    }
}
