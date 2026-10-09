<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportVersion>
 */
class ReportVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'version_number' => 1,
            'status' => ReportStatus::Draft->value,
            'generated_by' => User::factory()->student(),
            'generated_at' => now(),
            'source_snapshot' => ['activities' => [], 'projects' => []],
            'generation_notes' => null,
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::Final->value,
            'edited_at' => null,
        ]);
    }
}
