<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Cohort;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'cohort_id' => Cohort::factory(),
            'report_template_id' => ReportTemplate::factory(),
            'title' => 'SIWES Technical Report',
            'status' => ReportStatus::Draft->value,
            'current_version' => 0,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::Submitted->value,
            'submitted_at' => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::UnderReview->value,
            'submitted_at' => now()->subDays(2),
        ]);
    }

    public function revisionRequested(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::RevisionRequested->value,
            'submitted_at' => now()->subDays(5),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::Approved->value,
            'submitted_at' => now()->subDays(10),
            'approved_at' => now()->subDays(2),
        ]);
    }

    public function final(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::Final->value,
            'submitted_at' => now()->subDays(20),
            'approved_at' => now()->subDays(10),
            'finalized_at' => now()->subDays(5),
        ]);
    }
}
