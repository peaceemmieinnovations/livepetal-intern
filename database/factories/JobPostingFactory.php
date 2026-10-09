<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\JobPosting;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->admin(),
            'title' => fake()->unique()->jobTitle(),
            'team' => fake()->randomElement(['Engineering', 'Product', 'Quality']),
            'employment_type' => 'full_time',
            'location' => 'Lagos, Nigeria',
            'is_remote' => false,
            'summary' => fake()->sentence(18),
            'description' => fake()->paragraphs(2, true),
            'responsibilities' => fake()->paragraph(3),
            'requirements' => fake()->paragraph(3),
            'benefits' => fake()->paragraph(2),
            'skill_ids' => [],
            'deadline' => now()->addWeeks(4),
            'is_accepting_applications' => true,
            'status' => ContentStatus::Published->value,
            'is_featured' => false,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'views_count' => fake()->numberBetween(0, 600),
        ];
    }

    public function withSkills(int $count = 3): static
    {
        return $this->afterCreating(function (JobPosting $posting) use ($count): void {
            $posting->update([
                'skill_ids' => Skill::factory()->count($count)->create()->pluck('id')->all(),
            ]);
        });
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Archived->value,
            'deadline' => now()->subWeek(),
        ]);
    }

    /**
     * Published and past its deadline, but an administrator has paused new
     * submissions. The listing stays visible; the Apply button disappears.
     */
    public function applicationsPaused(): static
    {
        return $this->state(fn (): array => [
            'is_accepting_applications' => false,
        ]);
    }
}
