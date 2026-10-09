<?php

namespace Database\Factories;

use App\Models\SupervisorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupervisorProfile>
 */
class SupervisorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->supervisor(),
            'job_title' => fake()->randomElement([
                'Senior Software Engineer',
                'Team Lead, Engineering',
                'Technical Supervisor',
                'Head of Development',
            ]),
            'team' => fake()->randomElement(['Engineering', 'Platform', 'Delivery', 'Quality']),
            'bio' => fake()->paragraph(3),
            'specialisms' => ['Laravel', 'System design'],
            'accepting_reviews' => true,
        ];
    }
}
