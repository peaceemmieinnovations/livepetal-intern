<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Institution;
use App\Models\Placement;
use App\Models\Programme;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * One enrolled trainee with a profile, placement and supervisor so the dashboards
 * have real content to render, and so the approval flow has something to act on.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cohort = Cohort::query()->firstOrFail();
        $institution = Institution::query()->firstOrFail();
        $department = Department::query()->first();
        $programme = Programme::query()->first();

        $student = User::query()->updateOrCreate(
            ['email' => 'student@livepetalintern.test'],
            [
                'name' => 'Chidi Student',
                'role' => UserRole::Student->value,
                'status' => User::STATUS_ACTIVE,
                'onboarding_completed_at' => now(),
                'approved_at' => now(),
            ],
        );

        $profile = StudentProfile::query()->updateOrCreate(
            ['user_id' => $student->id],
            [
                'institution_id' => $institution->id,
                'department_id' => $department?->id,
                'programme_id' => $programme?->id,
                'cohort_id' => $cohort->id,
                'student_id_number' => 'SIWES-2026-0001',
                'level' => '300',
                'graduation_year' => now()->year + 2,
                'bio' => 'Third-year computer science student on a six-month industrial training placement.',
                'learning_goals' => 'Ship production Laravel features, learn to review code properly, and document my work well enough to teach it.',
                'professional_interests' => ['Backend engineering', 'Developer tooling'],
                'is_public' => true,
                'approved_at' => now(),
            ],
        );

        $supervisor = User::query()->updateOrCreate(
            ['email' => 'supervisor@livepetalintern.test'],
            [
                'name' => 'Ada Supervisor',
                'role' => UserRole::Supervisor->value,
                'status' => User::STATUS_ACTIVE,
                'approved_at' => now(),
            ],
        );

        SupervisorProfile::query()->updateOrCreate(
            ['user_id' => $supervisor->id],
            ['job_title' => 'Senior Software Engineer', 'team' => 'Engineering'],
        );

        Placement::query()->updateOrCreate(
            ['student_id' => $student->id, 'cohort_id' => $cohort->id],
            [
                'supervisor_id' => $supervisor->id,
                'training_area' => 'Backend',
                'unit' => 'Engineering',
                'starts_on' => now()->startOfYear(),
                'ends_on' => now()->startOfYear()->addMonths(6),
                'status' => Placement::STATUS_ACTIVE,
                'approved_at' => now(),
            ],
        );
    }
}
