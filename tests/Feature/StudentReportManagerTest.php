<?php

use App\Enums\ReportStatus;
use App\Models\ActivityLog;
use App\Models\Placement;
use App\Models\Project;
use App\Models\ReportTemplate;
use App\Models\ReportVersion;
use App\Models\User;
use Livewire\Livewire;

function reportManagerStudent(): User
{
    $student = User::factory()->student()->withStudentProfile()->create();
    Placement::factory()->active()->create(['student_id' => $student->id]);

    return $student;
}

test('a student can open the report manager', function () {
    $student = reportManagerStudent();

    $this->actingAs($student)
        ->get(route('student.reports.index'))
        ->assertOk()
        ->assertSee('Report manager');
});

test('roles without intern or student access cannot open the report manager', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->admin()->create(),
        User::factory()->supervisor()->create(),
    ] as $user) {
        $this->actingAs($user)
            ->get(route('student.reports.index'))
            ->assertForbidden();
    }
});

test('generating a draft builds sections only from real records', function () {
    $student = reportManagerStudent();
    $profileId = $student->studentProfile->id;

    ActivityLog::factory()->submitted()->create([
        'student_profile_id' => $profileId,
        'activity_date' => now()->subDays(3),
        'title' => 'Blueprinted the onboarding graph',
        'description' => 'Mapped the sign-up flow.',
        'technologies' => ['Laravel', 'Livewire'],
    ]);
    Project::factory()->create([
        'student_profile_id' => $profileId,
        'name' => 'SIWES Tracker',
    ]);
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $student->studentProfile->reports()->first();

    expect($report)->not->toBeNull()
        ->and($report->status)->toBe(ReportStatus::Draft)
        ->and($report->current_version)->toBe(1);

    $activities = $report->currentVersion->sections->firstWhere('type', 'chapter_activities');

    expect($activities->body)->toContain('Blueprinted the onboarding graph')
        ->and($activities->body)->toContain('reviewed');

    $organization = $report->currentVersion->sections->firstWhere('type', 'chapter_organization');

    expect($organization->body)->toContain('[')
        ->and($organization->placeholder_note)->not->toBeNull();
});

test('the draft does not require source records to be generated', function () {
    $student = reportManagerStudent();
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $student->studentProfile->reports()->first();

    expect(ReportVersion::where('report_id', $report->id)->count())->toBe(1)
        ->and($report->currentVersion->sections->count())->toBe(11);
});

test('regenerating preserves sections the student edited', function () {
    $student = reportManagerStudent();
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $student->studentProfile->reports()->first();
    $intro = $report->currentVersion->sections->firstWhere('type', 'chapter_introduction');

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->set('contents.'.$intro->id, 'My carefully written introduction.')
        ->call('saveSection', $intro->id);

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report->refresh();
    $latest = $report->latestVersion();

    $moved = $latest->sections->firstWhere('type', 'chapter_introduction');

    expect($report->current_version)->toBe(2)
        ->and($moved->body)->toBe('My carefully written introduction.')
        ->and($moved->is_edited)->toBeTrue();
});

test('requesting review locks the draft for editing', function () {
    $student = reportManagerStudent();
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $student->studentProfile->reports()->first();
    $section = $report->currentVersion->sections->first();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('requestReview');

    $report->refresh();

    expect($report->status)->toBe(ReportStatus::Submitted)
        ->and($report->submitted_at)->not->toBeNull();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->set('contents.'.$section->id, 'Sneaky edit.')
        ->call('saveSection', $section->id);

    $report->refresh();

    expect(ReportVersion::where('report_id', $report->id)->count())->toBe(1);

    $unchanged = $report->latestVersion()->sections->firstWhere('id', $section->id);

    expect($unchanged->body)->not->toBe('Sneaky edit.');
});

test('the print route is available to the owner, admin and their supervisor', function () {
    $student = reportManagerStudent();
    $supervisor = User::factory()->supervisor()->create();
    $student->placement()->update(['supervisor_id' => $supervisor->id]);
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($student)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $student->studentProfile->reports()->first();

    $this->actingAs($student)->get(route('reports.print', $report))->assertOk()->assertSee($report->title);
    $this->actingAs($supervisor)->get(route('reports.print', $report))->assertOk();
    $this->actingAs(User::factory()->admin()->create())->get(route('reports.print', $report))->assertOk();
});

test('another student cannot print a report they do not own', function () {
    $owner = reportManagerStudent();
    $other = User::factory()->student()->withStudentProfile()->create();
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($owner)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $owner->studentProfile->reports()->first();

    $this->actingAs($other)
        ->get(route('reports.print', $report))
        ->assertForbidden();
});

test('an unwatched supervisor cannot print another supervisee report', function () {
    $owner = reportManagerStudent();
    $stranger = User::factory()->supervisor()->create();
    ReportTemplate::factory()->default()->create();

    Livewire::actingAs($owner)
        ->test('pages::student-reports')
        ->call('generate');

    $report = $owner->studentProfile->reports()->first();

    $this->actingAs($stranger)
        ->get(route('reports.print', $report))
        ->assertForbidden();
});
