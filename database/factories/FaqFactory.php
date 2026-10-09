<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(8),
            'answer' => fake()->paragraph(2),
            'category' => fake()->randomElement(['General', 'Reports', 'Community', 'Applications']),
            'sort_order' => fake()->numberBetween(0, 30),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
