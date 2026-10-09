<?php

namespace Database\Factories;

use App\Enums\ModuleContentType;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(2, true),
            'content_type' => ModuleContentType::Text->value,
            'external_url' => null,
            'duration_minutes' => fake()->numberBetween(5, 45),
            'sort_order' => 1,
            'is_published' => true,
        ];
    }

    public function video(string $url = 'https://www.youtube.com/watch?v=example'): static
    {
        return $this->state(fn (): array => [
            'content_type' => ModuleContentType::Video->value,
            'content' => null,
            'external_url' => $url,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
