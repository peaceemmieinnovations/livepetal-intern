<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'bio' => fake()->paragraph(3),
            'phone' => fake()->numerify('080########'),
            'location' => fake()->city().', Nigeria',
            'level' => fake()->randomElement(['100', '200', '300', '400', '500']),
            'graduation_year' => now()->year + fake()->numberBetween(1, 4),
            'student_id_number' => (string) fake()->unique()->numberBetween(100000, 999999),
            'learning_goals' => fake()->sentence(12),
            'professional_interests' => ['Backend engineering', 'UI design'],
            'portfolio_links' => ['https://github.com/example'],
            'github_url' => 'https://github.com/example',
            'is_public' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['approved_at' => now()]);
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'is_public' => true,
            'approved_at' => now(),
        ]);
    }
}
