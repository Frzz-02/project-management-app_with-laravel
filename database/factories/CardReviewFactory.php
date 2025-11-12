<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\User;
use App\Models\CardReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardReview>
 */
class CardReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['approved', 'rejected']);
        
        return [
            'card_id' => Card::factory(),
            'reviewed_by' => User::factory(),
            'status' => $status,
            'notes' => $status === 'rejected' 
                ? fake()->sentence(10) // Rejected biasanya ada notes
                : (fake()->boolean(30) ? fake()->sentence(8) : null), // Approved 30% ada notes
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * State untuk review yang approved
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'notes' => fake()->boolean(30) ? fake()->sentence(8) : null,
        ]);
    }

    /**
     * State untuk review yang rejected
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'notes' => fake()->sentence(10), // Rejected selalu ada notes
        ]);
    }

    /**
     * State untuk review dengan notes
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->paragraph(2),
        ]);
    }

    /**
     * State untuk review tanpa notes
     */
    public function withoutNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => null,
        ]);
    }
}
