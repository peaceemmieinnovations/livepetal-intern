<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogComment>
 */
class BlogCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_post_id' => BlogPost::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'guest_name' => null,
            'guest_email' => null,
            'body' => fake()->sentence(12),
            'status' => CommentStatus::Published->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Pending->value]);
    }

    public function guest(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
        ]);
    }
}
