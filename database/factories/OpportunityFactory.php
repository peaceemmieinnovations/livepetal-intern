<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\OpportunityType;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->catchPhrase();

        return [
            'created_by' => User::factory()->admin(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'organization' => fake()->randomElement([
                'Livepetal Systems Limited',
                'Open Source Community',
                'Student Innovation Fund',
                'Local Tech Startup',
            ]),
            'description' => fake()->paragraph(3),
            'type' => fake()->randomElement(OpportunityType::cases())->value,
            'location' => fake()->randomElement(['Lagos, Nigeria', 'Remote', 'Abuja, Nigeria']),
            'is_remote' => false,
            'deadline' => now()->addWeeks(3),
            'external_url' => 'https://example.com/apply',
            'status' => ContentStatus::Published->value,
            'published_at' => now()->subDays(fake()->numberBetween(0, 20)),
            'views_count' => fake()->numberBetween(0, 400),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => ['deadline' => now()->subWeek()]);
    }
}
