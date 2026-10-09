<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Programme>
 */
class ProgrammeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'B.Sc. Computer Science',
            'B.Sc. Software Engineering',
            'B.Sc. Information Technology',
            'B.Eng. Computer Engineering',
            'B.Sc. Cybersecurity',
        ]);

        return [
            'department_id' => Department::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(3)),
            'code' => Str::upper(Str::random(5)),
            'level' => fake()->randomElement(['100', '200', '300', '400', '500']),
            'duration_years' => fake()->numberBetween(4, 6),
            'is_active' => true,
        ];
    }
}
