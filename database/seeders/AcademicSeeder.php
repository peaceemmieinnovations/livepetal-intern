<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Institution;
use App\Models\Programme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Reference academic data. Universities and their departments are records the
 * admin manages, not hard-coded configuration.
 */
class AcademicSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const STRUCTURE = [
        'University of Lagos' => [
            'Systems Engineering',
            'Computer Science',
        ],
        'Lagos State University' => [
            'Computer Science',
            'Software Engineering',
        ],
        'Obafemi Awolowo University' => [
            'Computer Science and Engineering',
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const PROGRAMMES = [
        'Computer Science' => ['B.Sc. Computer Science', 'B.Sc. Information Technology'],
        'Systems Engineering' => ['B.Sc. Systems Engineering'],
        'Software Engineering' => ['B.Sc. Software Engineering'],
        'Computer Science and Engineering' => ['B.Eng. Computer Engineering'],
    ];

    public function run(): void
    {
        foreach (self::STRUCTURE as $institutionName => $departments) {
            $institution = Institution::query()->updateOrCreate(
                ['slug' => Str::slug($institutionName)],
                [
                    'name' => $institutionName,
                    'short_name' => Str::upper(implode('', array_map(
                        fn (string $word): string => Str::substr($word, 0, 1),
                        explode(' ', $institutionName),
                    ))),
                    'location' => 'Nigeria',
                    'is_active' => true,
                ],
            );

            foreach ($departments as $departmentName) {
                $department = Department::query()->updateOrCreate(
                    ['slug' => Str::slug($departmentName).'-'.$institution->id],
                    [
                        'institution_id' => $institution->id,
                        'name' => $departmentName,
                        'code' => Str::upper(Str::substr(Str::slug($departmentName), 0, 3)),
                        'is_active' => true,
                    ],
                );

                foreach (self::PROGRAMMES[$departmentName] ?? [] as $programmeName) {
                    Programme::query()->updateOrCreate(
                        ['slug' => Str::slug($programmeName).'-'.$department->id],
                        [
                            'department_id' => $department->id,
                            'name' => $programmeName,
                            'code' => Str::upper(Str::substr(str_replace(['B.Sc.', 'B.Eng.', ' '], '', $programmeName), 0, 5)),
                            'level' => '300',
                            'duration_years' => 5,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }
}
