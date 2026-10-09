<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reflection>
 */
class ReflectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'project_id' => Project::factory(),
            'skill_id' => Skill::factory(),
            'reflection_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'title' => fake()->sentence(4),
            'learned' => fake()->paragraph(2),
            'challenge' => fake()->sentence(12),
            'resolution' => fake()->sentence(10),
            'improvements' => fake()->paragraph(2),
            'body' => fake()->paragraph(3),
            'visibility' => Visibility::Private->value,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (): array => ['visibility' => Visibility::Community->value]);
    }
}
