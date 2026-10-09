<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => 'site.'.fake()->unique()->slug(2),
            'value' => fake()->word(),
            'group' => 'general',
            'type' => 'string',
        ];
    }

    public function group(string $group): static
    {
        return $this->state(fn (array $attributes): array => [
            'group' => $group,
            'key' => "{$group}.".fake()->unique()->slug(2),
        ]);
    }
}
