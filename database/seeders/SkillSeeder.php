<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The skill taxonomy trainees build their profile against.
 */
class SkillSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const SKILLS = [
        'backend' => ['PHP', 'Laravel', 'REST API Design', 'Queue & Jobs', 'Testing with PHPUnit'],
        'frontend' => ['JavaScript', 'TypeScript', 'Vue.js', 'Livewire', 'Tailwind CSS'],
        'database' => ['MySQL', 'Database Design', 'Query Optimisation'],
        'devops' => ['Git', 'Docker', 'Linux', 'CI/CD'],
        'design' => ['Figma', 'UI Design', 'Accessibility'],
        'practitioner' => ['Technical Writing', 'Code Review', 'Mentoring', 'Requirements Gathering'],
    ];

    public function run(): void
    {
        foreach (self::SKILLS as $category => $skills) {
            foreach ($skills as $skill) {
                Skill::query()->updateOrCreate(
                    ['slug' => Str::slug($skill)],
                    [
                        'name' => $skill,
                        'category' => $category,
                        'description' => "{$skill} as applied in day-to-day engineering work at Livepetal.",
                        'is_active' => true,
                    ],
                );
            }
        }

        Skill::query()->updateOrCreate(
            ['slug' => Str::slug('Communication')],
            [
                'name' => 'Communication',
                'category' => 'practitioner',
                'description' => 'Clear written and verbal communication.',
                'is_active' => true,
            ],
        );
    }
}
