<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityComment>
 */
class CommunityCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'community_post_id' => CommunityPost::factory(),
            'user_id' => User::factory()->student(),
            'parent_id' => null,
            'body' => fake()->sentence(12),
            'status' => CommentStatus::Published->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Pending->value]);
    }
}
