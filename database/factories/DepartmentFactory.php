<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Computer Science',
            'Software Engineering',
            'Information Technology',
            'Computer Engineering',
            'Electrical Engineering',
            'Mathematics',
        ]).' Department';

        return [
            'institution_id' => Institution::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(3)),
            'code' => Str::upper(Str::random(3)),
            'is_active' => true,
        ];
    }
}
