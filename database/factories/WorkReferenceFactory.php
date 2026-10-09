<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Documents work carried out under external supervision, such as a university
 * project or an internship elsewhere, so it is not lost when the placement ends.
 *
 * @extends Factory<WorkReference>
 */
class WorkReferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'project_id' => Project::factory(),
            'work_title' => fake()->sentence(4),
            'worked_on' => now()->subMonths(fake()->numberBetween(1, 6)),
            'short_description' => fake()->paragraph(2),
            'outcome' => fake()->sentence(12),
            'evidence_link' => 'https://example.com/evidence',
            'visibility' => Visibility::Private->value,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (): array => ['visibility' => Visibility::Community->value]);
    }
}
