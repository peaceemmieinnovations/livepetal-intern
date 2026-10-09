<?php

namespace Database\Factories;

use App\Enums\FeedbackCategory;
use App\Models\StudentProfile;
use App\Models\SupervisorFeedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupervisorFeedback>
 */
class SupervisorFeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'category' => fake()->randomElement(FeedbackCategory::cases())->value,
            'comments' => fake()->paragraph(3),
            'rating' => fake()->numberBetween(3, 5),
            'recommendations' => fake()->sentence(12),
            'is_shared_with_student' => false,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (): array => ['is_shared_with_student' => true]);
    }
}
