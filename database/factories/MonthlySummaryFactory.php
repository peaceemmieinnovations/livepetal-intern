<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlySummary>
 */
class MonthlySummaryFactory extends Factory
{
    public function definition(): array
    {
        $monthStart = now()->subMonths(fake()->numberBetween(1, 4))->startOfMonth();

        return [
            'student_profile_id' => StudentProfile::factory(),
            'month_starts_on' => $monthStart,
            'month_ends_on' => $monthStart->copy()->endOfMonth(),
            'summary' => fake()->paragraph(4),
            'major_learning' => fake()->paragraph(2),
            'challenges' => fake()->paragraph(2),
            'achievements' => fake()->paragraph(2),
            'student_reflection' => fake()->paragraph(2),
            'supervisor_feedback' => null,
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
}
