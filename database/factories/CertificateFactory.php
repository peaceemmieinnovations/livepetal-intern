<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use App\Models\Certificate;
use App\Models\Document;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'title' => fake()->randomElement([
                'SIWES Completion Certificate',
                'Laravel Fundamentals Certificate',
                'Introduction to Git Certificate',
            ]),
            'issuer' => 'Livepetal Systems Limited',
            'issued_on' => now()->subMonths(1),
            'credential_url' => null,
            'document_id' => Document::factory(),
            'visibility' => Visibility::Private->value,
            'review_status' => ReviewStatus::Pending->value,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'review_status' => ReviewStatus::Verified->value,
            'verified_at' => now(),
        ]);
    }

    public function onPortfolio(): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::PublicPortfolio->value,
            'review_status' => ReviewStatus::Verified->value,
            'verified_at' => now(),
        ]);
    }
}
