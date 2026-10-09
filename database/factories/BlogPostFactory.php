<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'blog_category_id' => BlogCategory::factory(),
            'author_id' => User::factory()->admin(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'excerpt' => fake()->sentence(20),
            'body' => '<p>'.fake()->paragraphs(4, true).'</p>',
            'cover_image_path' => null,
            'meta_description' => Str::limit(fake()->sentence(12), 150),
            'status' => ContentStatus::Published->value,
            'is_featured' => false,
            'views_count' => fake()->numberBetween(0, 800),
            'reading_minutes' => fake()->numberBetween(2, 12),
            'published_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (BlogPost $post) use ($count): void {
            $post->tags()->sync(Tag::factory()->count($count)->create());
        });
    }
}
