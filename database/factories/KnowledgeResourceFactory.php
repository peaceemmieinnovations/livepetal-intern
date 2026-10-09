<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\Visibility;
use App\Models\KnowledgeResource;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeResource>
 */
class KnowledgeResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'skill_id' => Skill::factory(),
            'reviewed_by' => null,
            'title' => fake()->unique()->sentence(5),
            'summary' => fake()->sentence(18),
            'body' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(array_keys(KnowledgeResource::typeOptions())),
            'tags' => ['laravel'],
            'status' => ContentStatus::Draft->value,
            'visibility' => Visibility::Community->value,
            'contains_company_confidential' => false,
            'helpful_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Published->value,
            'reviewed_by' => User::factory()->admin(),
            'published_at' => now(),
        ]);
    }

    public function confidential(): static
    {
        return $this->state(fn (): array => [
            'contains_company_confidential' => true,
        ]);
    }
}
