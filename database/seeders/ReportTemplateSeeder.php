<?php

namespace Database\Seeders;

use App\Enums\ReportSectionType;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The report outline trainees generate against. Chapter order lives in the
 * database so it can be adjusted per cohort without a code change.
 */
class ReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('role', 'super_admin')->first()
            ?? User::query()->where('role', 'admin')->first();

        $templates = [
            [
                'name' => 'Standard SIWES Report',
                'slug' => 'standard-siwes-report',
                'description' => 'The default six-month SIWES technical report structure used by most partner institutions.',
                'sections' => [
                    ReportSectionType::CoverPage,
                    ReportSectionType::Certification,
                    ReportSectionType::Dedication,
                    ReportSectionType::Acknowledgement,
                    ReportSectionType::TableOfContents,
                    ReportSectionType::ChapterIntroduction,
                    ReportSectionType::ChapterOrganization,
                    ReportSectionType::ChapterActivities,
                    ReportSectionType::ChapterProject,
                    ReportSectionType::ChapterConclusion,
                    ReportSectionType::Appendices,
                ],
                'is_default' => true,
            ],
            [
                'name' => 'Short Engineering Log Report',
                'slug' => 'short-engineering-log-report',
                'description' => 'A condensed structure for trainees whose institution requires a log-based report.',
                'sections' => [
                    ReportSectionType::CoverPage,
                    ReportSectionType::Certification,
                    ReportSectionType::ChapterIntroduction,
                    ReportSectionType::ChapterActivities,
                    ReportSectionType::ChapterProject,
                    ReportSectionType::ChapterConclusion,
                ],
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            ReportTemplate::query()->updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'sections' => array_map(
                        fn (ReportSectionType $section): string => $section->value,
                        $template['sections'],
                    ),
                    'requirements' => [
                        'activities' => 20,
                        'weekly_summaries' => 4,
                        'monthly_summaries' => 2,
                        'projects' => 1,
                        'skills' => 3,
                        'supervisor_feedback' => 1,
                        'documents' => 1,
                    ],
                    'is_default' => $template['is_default'],
                    'created_by' => $author?->id,
                ],
            );
        }
    }
}
