<?php

namespace Database\Factories;

use App\Enums\SkillLevel;
use App\Models\Project;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentSkill>
 */
class StudentSkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'skill_id' => Skill::factory(),
            'level' => fake()->randomElement(SkillLevel::cases())->value,
            'first_practiced_on' => now()->subMonths(3),
            'last_practiced_on' => now()->subWeek(),
            'evidence' => fake()->paragraph(2),
            'is_verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    public function evidencedByProject(): static
    {
        return $this->state(fn (): array => [
            'project_id' => Project::factory(),
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }
}
