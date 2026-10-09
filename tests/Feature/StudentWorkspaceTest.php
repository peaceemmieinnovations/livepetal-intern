<?php

use App\Enums\ActivityStatus;
use App\Enums\ProjectStatus;
use App\Enums\SkillLevel;
use App\Enums\Visibility;
use App\Models\ActivityLog;
use App\Models\Placement;
use App\Models\Project;
use App\Models\Skill;
use App\Models\StudentSkill;
use App\Models\User;
use Livewire\Livewire;

function workspaceStudent(): User
{
    $student = User::factory()->student()->withStudentProfile()->create();
    Placement::factory()->active()->create(['student_id' => $student->id]);

    return $student;
}

test('a student can open the activity log, projects and skills pages', function () {
    $student = workspaceStudent();

    foreach ([
        route('student.activities.index'),
        route('student.projects.index'),
        route('student.skills'),
    ] as $url) {
        $this->actingAs($student)->get($url)->assertOk();
    }
});

test('roles without student intern access cannot open the workspace', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->supervisor()->create(),
        User::factory()->admin()->create(),
    ] as $user) {
        $this->actingAs($user)->get(route('student.activities.index'))->assertForbidden();
        $this->actingAs($user)->get(route('student.projects.index'))->assertForbidden();
        $this->actingAs($user)->get(route('student.skills'))->assertForbidden();
    }
});

test('a student can log an activity as a draft and submit it for review', function () {
    $student = workspaceStudent();

    Livewire::actingAs($student)
        ->test('pages::student-activities')
        ->set('activity_date', now()->toDateString())
        ->set('title', 'Built the file upload')
        ->set('description', 'Added multipart upload with validation.')
        ->set('technologies', 'Laravel, Livewire')
        ->set('visibility', 'supervisor_only')
        ->call('save', false);

    $log = ActivityLog::first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(ActivityStatus::Draft)
        ->and($log->visibility)->toBe(Visibility::SupervisorOnly)
        ->and($log->technologies)->toBe(['Laravel', 'Livewire']);

    Livewire::actingAs($student)
        ->test('pages::student-activities')
        ->call('submitEntry', $log->id);

    $log->refresh();

    expect($log->status)->toBe(ActivityStatus::Submitted)
        ->and($log->submitted_at)->not->toBeNull();
});

test('a reviewed activity cannot be deleted', function () {
    $student = workspaceStudent();
    $log = ActivityLog::factory()->reviewed()->create([
        'student_profile_id' => $student->studentProfile->id,
    ]);

    Livewire::actingAs($student)
        ->test('pages::student-activities')
        ->call('deleteEntry', $log->id);

    expect(ActivityLog::find($log->id))->not->toBeNull();
});

test('a draft activity can be deleted', function () {
    $student = workspaceStudent();
    $log = ActivityLog::factory()->create([
        'student_profile_id' => $student->studentProfile->id,
    ]);

    Livewire::actingAs($student)
        ->test('pages::student-activities')
        ->call('deleteEntry', $log->id);

    expect(ActivityLog::find($log->id))->toBeNull();
});

test('a student can add a project and toggle its portfolio visibility', function () {
    $student = workspaceStudent();

    Livewire::actingAs($student)
        ->test('pages::student-projects')
        ->set('name', 'SIWES Dashboard')
        ->set('description', 'A dashboard for intern reporting.')
        ->set('technologies', 'Laravel, MySQL')
        ->call('save');

    $project = Project::first();

    expect($project)->not->toBeNull()
        ->and($project->name)->toBe('SIWES Dashboard')
        ->and($project->status)->toBe(ProjectStatus::InProgress)
        ->and($project->visibility)->toBe(Visibility::Private)
        ->and($project->slug)->not->toBeEmpty();

    Livewire::actingAs($student)
        ->test('pages::student-projects')
        ->call('togglePortfolio', $project->id);

    expect($project->refresh()->visibility)->toBe(Visibility::PublicPortfolio);
});

test('a student can update and delete their own project', function () {
    $student = workspaceStudent();
    $project = Project::factory()->create(['student_profile_id' => $student->studentProfile->id]);

    Livewire::actingAs($student)
        ->test('pages::student-projects')
        ->call('edit', $project->id)
        ->set('name', 'Renamed project')
        ->call('save');

    expect($project->refresh()->name)->toBe('Renamed project');

    Livewire::actingAs($student)
        ->test('pages::student-projects')
        ->call('delete', $project->id);

    expect(Project::find($project->id))->toBeNull();
});

test('a student can add a skill with self-assessed level and remove it', function () {
    $student = workspaceStudent();

    Livewire::actingAs($student)
        ->test('pages::student-skills')
        ->set('name', 'Laravel')
        ->set('level', 'competent')
        ->call('add');

    $entry = StudentSkill::first();

    expect($entry)->not->toBeNull()
        ->and($entry->level)->toBe(SkillLevel::Competent)
        ->and($entry->skill->name)->toBe('Laravel');

    Livewire::actingAs($student)
        ->test('pages::student-skills')
        ->call('remove', $entry->id);

    expect(StudentSkill::find($entry->id))->toBeNull();
});

test('duplicate skills are rejected', function () {
    $student = workspaceStudent();
    $skill = Skill::create(['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'general']);
    StudentSkill::create([
        'student_profile_id' => $student->studentProfile->id,
        'skill_id' => $skill->id,
        'level' => SkillLevel::Beginner->value,
    ]);

    Livewire::actingAs($student)
        ->test('pages::student-skills')
        ->set('name', 'Laravel')
        ->set('level', 'beginner')
        ->call('add')
        ->assertHasErrors('name');

    expect(StudentSkill::count())->toBe(1);
});