<?php

namespace Database\Factories;

use App\Models\Cohort;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cohort>
 */
class CohortFactory extends Factory
{
    public function definition(): array
    {
        $year = now()->year;
        $starts = now()->startOfYear();

        return [
            'name' => "{$year} SIWES Cohort",
            'slug' => "{$year}-siwes-cohort",
            'academic_year' => "{$year}/".($year + 1),
            'starts_on' => $starts,
            'ends_on' => $starts->copy()->addMonths(6),
            'status' => Cohort::STATUS_ACTIVE,
            'notes' => null,
        ];
    }

    public function planned(): static
    {
        return $this->state(fn (): array => [
            'status' => Cohort::STATUS_PLANNED,
            'starts_on' => now()->addMonths(2),
            'ends_on' => now()->addMonths(8),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => Cohort::STATUS_COMPLETED,
            'starts_on' => now()->subMonths(8),
            'ends_on' => now()->subMonths(2),
        ]);
    }
}
