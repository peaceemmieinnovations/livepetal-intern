<?php

namespace Database\Factories;

use App\Models\ReportReview;
use App\Models\ReportVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportReview>
 */
class ReportReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'report_version_id' => ReportVersion::factory(),
            'reviewer_id' => User::factory()->supervisor(),
            'decision' => ReportReview::DECISION_COMMENTED,
            'comments' => fake()->paragraph(2),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'decision' => ReportReview::DECISION_APPROVED,
            'comments' => 'Strong progress. Approved.',
        ]);
    }

    public function revisionsRequested(): static
    {
        return $this->state(fn (): array => [
            'decision' => ReportReview::DECISION_REVISIONS,
            'comments' => 'Please expand chapter three with detail from your activity log.',
        ]);
    }
}
