<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'PHP', 'Laravel', 'Vue.js', 'JavaScript', 'TypeScript', 'MySQL', 'Git',
            'REST API Design', 'Tailwind CSS', 'React', 'Docker', 'Linux', 'Figma',
            'Automated Testing', 'Database Design', 'Technical Writing',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['backend', 'frontend', 'database', 'devops', 'design', 'practitioner']),
            'description' => fake()->sentence(10),
            'is_active' => true,
        ];
    }

    public function in(string $category): static
    {
        return $this->state(fn (): array => ['category' => $category]);
    }
}
