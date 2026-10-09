<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Document;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'user_id' => null,
            'document_id' => Document::factory(),
            'applicant_name' => fake()->name(),
            'applicant_email' => fake()->unique()->safeEmail(),
            'applicant_phone' => fake()->numerify('080########'),
            'portfolio_url' => 'https://example.com',
            'linkedin_url' => 'https://linkedin.com/in/example',
            'cover_note' => fake()->paragraph(2),
            'status' => ApplicationStatus::Submitted->value,
        ];
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
        ]);
    }

    public function shortlisted(): static
    {
        return $this->state(fn (): array => ['status' => ApplicationStatus::Shortlisted->value]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => ['status' => ApplicationStatus::Rejected->value]);
    }
}
