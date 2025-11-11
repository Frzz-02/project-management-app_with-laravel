<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_name' => fake()->sentence(3), // Slug akan auto-generated dari project_name via Observer
            'description' => fake()->paragraph(),
            'created_by' => 1,
            'deadline' => fake()->dateTimeBetween('now', '+1 year'),
            'created_at' => now(),
        ];
    }
}
