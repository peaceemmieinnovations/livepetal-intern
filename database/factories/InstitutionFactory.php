<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' University';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(3)),
            'short_name' => Str::upper(Str::random(3)).' Uni',
            'location' => fake()->city().', Nigeria',
            'website' => 'https://example.edu.ng',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
