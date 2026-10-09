<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the whole platform. Every seeder is idempotent, so running it twice is
 * safe and never duplicates records.
 *
 * Order matters: institutions and cohorts are referenced by profiles and
 * placements, and skills are referenced by courses.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AcademicSeeder::class,
            CohortSeeder::class,
            SkillSeeder::class,
            ReportTemplateSeeder::class,
            SettingsSeeder::class,
            CourseSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
