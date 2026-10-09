<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Enums\CommunityPostType;
use App\Enums\Visibility;
use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'project_id' => null,
            'post_type' => CommunityPostType::Discussion->value,
            'body' => fake()->paragraph(2),
            'tags' => ['laravel'],
            'visibility' => Visibility::Community->value,
            'status' => CommentStatus::Published->value,
            'likes_count' => 0,
            'comments_count' => 0,
            'is_pinned' => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (): array => [
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    public function problem(): static
    {
        return $this->state(fn (): array => [
            'post_type' => CommunityPostType::Problem->value,
        ]);
    }

    public function solved(User $solver): static
    {
        return $this->state(fn (): array => [
            'post_type' => CommunityPostType::Problem->value,
            'is_solved' => true,
            'solved_by' => $solver->id,
            'solved_at' => now(),
        ]);
    }

    public function aboutProject(Project $project): static
    {
        return $this->state(fn (): array => ['project_id' => $project->id]);
    }

    public function popular(): static
    {
        return $this->state(fn (): array => [
            'likes_count' => fake()->numberBetween(20, 200),
            'comments_count' => fake()->numberBetween(5, 40),
            'created_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }
}
