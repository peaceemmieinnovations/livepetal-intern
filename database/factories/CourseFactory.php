<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\CourseLevel;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->catchPhrase();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'summary' => fake()->sentence(16),
            'description' => fake()->paragraphs(2, true),
            'level' => fake()->randomElement(CourseLevel::cases())->value,
            'category_id' => null,
            'estimated_minutes' => fake()->numberBetween(60, 480),
            'status' => ContentStatus::Published->value,
            'created_by' => User::factory()->admin(),
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function aboutSkill(Skill $skill): static
    {
        return $this->state(fn (): array => ['category_id' => $skill->id]);
    }

    /**
     * A course with the given number of published, ordered modules.
     */
    public function withModules(int $count = 3): static
    {
        return $this->afterCreating(function (Course $course) use ($count): void {
            foreach (range(1, $count) as $order) {
                CourseModule::factory()->create([
                    'course_id' => $course->id,
                    'sort_order' => $order,
                ]);
            }
        });
    }
}
