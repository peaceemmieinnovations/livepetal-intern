<?php

namespace Database\Seeders;

use App\Models\Cohort;
use Illuminate\Database\Seeder;

/**
 * The current training cohort. Everything scoped to a training period (placements,
 * reports, announcements) hangs off this.
 */
class CohortSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        Cohort::query()->updateOrCreate(
            ['slug' => "{$year}-siwes-cohort"],
            [
                'name' => "{$year} SIWES Cohort",
                'academic_year' => "{$year}/".($year + 1),
                'starts_on' => now()->startOfYear(),
                'ends_on' => now()->startOfYear()->addMonths(6),
                'status' => Cohort::STATUS_ACTIVE,
                'notes' => 'Six-month industrial training placement at Livepetal Systems Limited.',
            ],
        );

        Cohort::query()->updateOrCreate(
            ['slug' => ($year - 1).'-siwes-cohort'],
            [
                'name' => ($year - 1).' SIWES Cohort',
                'academic_year' => ($year - 1).'/'.($year),
                'starts_on' => now()->startOfYear()->subYear(),
                'ends_on' => now()->startOfYear()->subYear()->addMonths(6),
                'status' => Cohort::STATUS_COMPLETED,
                'notes' => 'Previous cohort, retained for history.',
            ],
        );
    }
}
