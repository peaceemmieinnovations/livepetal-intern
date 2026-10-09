<?php

namespace Database\Factories;

use App\Models\CommunityPost;
use App\Models\CommunityPostLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPostLike>
 */
class CommunityPostLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'community_post_id' => CommunityPost::factory(),
            'user_id' => User::factory()->student(),
        ];
    }
}
