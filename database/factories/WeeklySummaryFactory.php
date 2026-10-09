<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WeeklySummary>
 */
class WeeklySummaryFactory extends Factory
{
    public function definition(): array
    {
        $weekStart = Carbon::instance(now()->startOfWeek())->subWeeks(fake()->numberBetween(1, 6));

        return [
            'student_profile_id' => StudentProfile::factory(),
            'week_starts_on' => $weekStart,
            'week_ends_on' => $weekStart->copy()->addDays(6),
            'summary' => fake()->paragraph(3),
            'major_learning' => fake()->paragraph(2),
            'challenges' => fake()->sentence(12),
            'achievements' => fake()->paragraph(2),
            'next_week_goals' => fake()->sentence(10),
            'activity_ids' => [],
            'skill_ids' => [],
            'project_ids' => [],
            'status' => ActivityStatus::Draft->value,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => [
            'status' => ActivityStatus::Submitted->value,
            'submitted_at' => now(),
        ]);
    }

    public function reviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => ActivityStatus::Published->value,
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now(),
        ]);
    }
}
