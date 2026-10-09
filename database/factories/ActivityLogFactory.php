<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Enums\Visibility;
use App\Models\Project;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'project_id' => Project::factory(),
            'activity_date' => now()->subDays(fake()->numberBetween(1, 40)),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'learned' => fake()->paragraph(2),
            'challenge' => fake()->sentence(12),
            'resolution' => fake()->sentence(10),
            'reflection' => fake()->paragraph(2),
            'technologies' => ['Laravel', 'MySQL'],
            'external_links' => null,
            'visibility' => Visibility::Private->value,
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
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now(),
        ]);
    }

    public function shared(): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::Community->value,
        ]);
    }
}
