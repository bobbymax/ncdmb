<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'slots' => $this->faker->randomDigit(),
            'access_level' => $this->faker->randomElement(['basic', 'operative', 'control', 'command', 'sovereign', 'system'])
        ];
    }
}
