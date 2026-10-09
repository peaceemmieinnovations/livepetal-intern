<?php

namespace Database\Factories;

use App\Models\Cohort;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Placement>
 */
class PlacementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'cohort_id' => Cohort::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'training_area' => fake()->randomElement(['Backend', 'Frontend', 'Full stack', 'Quality assurance']),
            'unit' => 'Engineering',
            'starts_on' => now()->startOfYear(),
            'ends_on' => now()->startOfYear()->addMonths(6),
            'status' => Placement::STATUS_ACTIVE,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => Placement::STATUS_ACTIVE,
            'starts_on' => now()->subDays(45),
            'ends_on' => now()->addMonths(4),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => Placement::STATUS_PENDING]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => Placement::STATUS_COMPLETED,
            'starts_on' => now()->subMonths(8),
            'ends_on' => now()->subMonths(2),
        ]);
    }
}
