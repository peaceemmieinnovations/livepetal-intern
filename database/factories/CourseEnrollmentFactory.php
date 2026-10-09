<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseEnrollment>
 */
class CourseEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'enrolled_at' => now()->subDays(fake()->numberBetween(0, 14)),
            'progress_percent' => 0,
        ];
    }

    public function complete(): static
    {
        return $this->state(fn (): array => [
            'progress_percent' => 100,
            'started_at' => now()->subDays(10),
            'completed_at' => now(),
            'last_accessed_at' => now(),
        ]);
    }
}
