<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase();

        return [
            'student_profile_id' => StudentProfile::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->paragraph(3),
            'problem' => fake()->sentence(14),
            'solution' => fake()->paragraph(2),
            'role' => fake()->randomElement(['Backend developer', 'Full stack developer', 'Contributor', 'Team lead']),
            'technologies' => ['Laravel', 'Livewire', 'MySQL'],
            'documentation' => null,
            'starts_on' => now()->subMonths(2),
            'ends_on' => null,
            'status' => ProjectStatus::InProgress->value,
            'repository_url' => 'https://github.com/example/project',
            'live_url' => null,
            'visibility' => Visibility::Private->value,
            'review_status' => ReviewStatus::Pending->value,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ProjectStatus::Completed->value,
            'ends_on' => now()->subWeek(),
            'live_url' => 'https://example.test',
        ]);
    }

    public function portfolioReady(): static
    {
        return $this->state(fn (): array => [
            'status' => ProjectStatus::Completed->value,
            'visibility' => Visibility::PublicPortfolio->value,
            'review_status' => ReviewStatus::Verified->value,
            'live_url' => 'https://example.test',
            'ends_on' => now()->subWeek(),
        ]);
    }

    public function usingSkills(): static
    {
        return $this->afterCreating(function (Project $project): void {
            $project->skills()->sync(Skill::factory()->count(3)->create());
        });
    }
}
