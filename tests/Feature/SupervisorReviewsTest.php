<?php

use App\Enums\ActivityStatus;
use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Models\ActivityLog;
use App\Models\Placement;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportReview;
use App\Models\ReportTemplate;
use App\Models\ReportVersion;
use App\Models\User;
use App\Models\WeeklySummary;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: User, 2: Placement}
 */
function supervisedPlacement(): array
{
    $supervisor = User::factory()->supervisor()->approved()->create();
    $student = User::factory()->student()->withStudentProfile()->create();
    $placement = Placement::factory()->active()->create([
        'student_id' => $student->id,
        'supervisor_id' => $supervisor->id,
    ]);

    return [$supervisor, $student, $placement];
}

test('a supervisor can open the supervisees page and sees their trainees', function () {
    [$supervisor, $student] = supervisedPlacement();

    $this->actingAs($supervisor)
        ->get(route('supervisor.students.index'))
        ->assertOk()
        ->assertSee($student->name);
});

test('roles without supervisor intern access cannot open the page', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->student()->create(),
        User::factory()->admin()->create(),
    ] as $user) {
        $this->actingAs($user)->get(route('supervisor.students.index'))->assertForbidden();
    }
});

test('a supervisor only sees their own trainees', function () {
    [$supervisor, $student] = supervisedPlacement();
    $stranger = User::factory()->supervisor()->approved()->create();

    $this->actingAs($supervisor)->get(route('supervisor.students.index'))->assertSee($student->name);
    $this->actingAs($stranger)->get(route('supervisor.students.index'))->assertOk()->assertDontSee($student->name);
});

test('a supervisor can approve a submitted activity', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $log = ActivityLog::factory()->submitted()->create([
        'student_profile_id' => $student->studentProfile->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('publishActivity', $log->id);

    $log->refresh();

    expect($log->status)->toBe(ActivityStatus::Published)
        ->and($log->reviewed_by)->toBe($supervisor->id)
        ->and($log->published_at)->not->toBeNull();
});

test('a supervisor can return an activity with feedback', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $log = ActivityLog::factory()->submitted()->create([
        'student_profile_id' => $student->studentProfile->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('openFeedback', $log->id)
        ->set('feedback', 'Expand the challenge and resolution.')
        ->call('returnActivity', $log->id);

    $log->refresh();

    expect($log->status)->toBe(ActivityStatus::Returned)
        ->and($log->reviewer_note)->toBe('Expand the challenge and resolution.')
        ->and($log->reviewed_by)->toBe($supervisor->id);
});

test('a supervisor can approve a weekly summary', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $summary = WeeklySummary::factory()->submitted()->create([
        'student_profile_id' => $student->studentProfile->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('publishSummary', $summary->id);

    $summary->refresh();

    expect($summary->status)->toBe(ActivityStatus::Published)
        ->and($summary->reviewed_by)->toBe($supervisor->id);
});

test('a supervisor can verify a project', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $project = Project::factory()->create([
        'student_profile_id' => $student->studentProfile->id,
        'review_status' => ReviewStatus::Pending->value,
    ]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('verifyProject', $project->id);

    $project->refresh();

    expect($project->review_status)->toBe(ReviewStatus::Verified)
        ->and($project->verified_by)->toBe($supervisor->id);
});

test('a supervisor can approve a submitted report', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $report = Report::factory()->submitted()->create([
        'student_profile_id' => $student->studentProfile->id,
        'cohort_id' => $placement->cohort_id,
        'report_template_id' => ReportTemplate::factory()->create()->id,
    ]);
    $version = ReportVersion::factory()->create([
        'report_id' => $report->id,
        'version_number' => 1,
        'status' => ReportStatus::Submitted->value,
    ]);
    $report->update(['current_version_id' => $version->id]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('approveReport', $report->id);

    $report->refresh();

    expect($report->status)->toBe(ReportStatus::Approved)
        ->and($report->approved_by)->toBe($supervisor->id)
        ->and($report->approved_at)->not->toBeNull();

    $review = ReportReview::where('report_version_id', $version->id)->first();

    expect($review)->not->toBeNull()
        ->and($review->decision)->toBe(ReportReview::DECISION_APPROVED)
        ->and($review->reviewer_id)->toBe($supervisor->id);
});

test('a supervisor can request revisions on a report with comments', function () {
    [$supervisor, $student, $placement] = supervisedPlacement();
    $report = Report::factory()->submitted()->create([
        'student_profile_id' => $student->studentProfile->id,
        'cohort_id' => $placement->cohort_id,
        'report_template_id' => ReportTemplate::factory()->create()->id,
    ]);
    $version = ReportVersion::factory()->create([
        'report_id' => $report->id,
        'version_number' => 1,
        'status' => ReportStatus::Submitted->value,
    ]);
    $report->update(['current_version_id' => $version->id]);

    Livewire::actingAs($supervisor)
        ->test('pages::supervisor-students')
        ->call('selectPlacement', $placement->id)
        ->call('openFeedback', $report->id)
        ->set('feedback', 'Add experimental design details to chapter four.')
        ->call('requestRevision', $report->id);

    $report->refresh();
    $review = ReportReview::where('report_version_id', $version->id)->first();

    expect($report->status)->toBe(ReportStatus::RevisionRequested)
        ->and($review->decision)->toBe(ReportReview::DECISION_REVISIONS)
        ->and($review->comments)->toBe('Add experimental design details to chapter four.');
});
