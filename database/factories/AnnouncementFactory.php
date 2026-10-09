<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Announcement;
use App\Models\Cohort;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(3),
            'audience' => 'all',
            'cohort_id' => null,
            'is_pinned' => false,
            'status' => ContentStatus::Published->value,
            'created_by' => User::factory()->admin(),
            'published_at' => now(),
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (): array => ['is_pinned' => true]);
    }

    public function forCohort(Cohort $cohort): static
    {
        return $this->state(fn (): array => [
            'cohort_id' => $cohort->id,
            'audience' => 'cohort',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Draft->value,
            'published_at' => null,
        ]);
    }
}
