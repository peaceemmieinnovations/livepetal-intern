<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseCertificate>
 */
class CourseCertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'recipient_name' => fake()->name(),
            'serial_number' => CourseCertificate::generateSerialNumber(),
            'issued_at' => now(),
        ];
    }

    /**
     * A certificate for a specific member, so the serial generator runs without
     * touching an unrelated account.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'recipient_name' => $user->name,
        ]);
    }
}
