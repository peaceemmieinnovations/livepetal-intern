<?php

use App\Enums\ActivityStatus;
use App\Models\User;
use App\Models\WeeklySummary;
use Livewire\Livewire;

function weeklySummarizer(): User
{
    return User::factory()->student()->withStudentProfile()->create();
}

function createWeek(User $student, array $overrides = []): WeeklySummary
{
    return WeeklySummary::factory()->create(array_merge([
        'student_profile_id' => $student->studentProfile->id,
        'week_starts_on' => now()->subWeek()->startOfWeek()->toDateString(),
        'week_ends_on' => now()->subWeek()->startOfWeek()->addDays(6)->toDateString(),
    ], $overrides));
}

test('a student can open the weekly summaries page', function () {
    $student = weeklySummarizer();

    $this->actingAs($student)
        ->get(route('student.weekly'))
        ->assertOk()
        ->assertSee('Weekly summaries');
});

test('roles without student intern access cannot open the weekly summaries page', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->admin()->create(),
        User::factory()->supervisor()->approved()->create(),
    ] as $user) {
        $this->actingAs($user)->get(route('student.weekly'))->assertForbidden();
    }
});

test('a student can save a week as a draft', function () {
    $student = weeklySummarizer();

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->set('week_starts_on', '2026-10-05')
        ->set('summary', 'Built the REST API for the inventory module.')
        ->set('major_learning', 'Resource controllers and API resources.')
        ->call('save', false);

    $week = WeeklySummary::where('student_profile_id', $student->studentProfile->id)->sole();

    expect($week->status)->toBe(ActivityStatus::Draft)
        ->and($week->summary)->toBe('Built the REST API for the inventory module.')
        ->and($week->week_starts_on->toDateString())->toBe('2026-10-05')
        ->and($week->week_ends_on->toDateString())->toBe('2026-10-11');
});

test('a student can submit a week for review', function () {
    $student = weeklySummarizer();

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->set('week_starts_on', '2026-10-05')
        ->set('summary', 'Wrapped up the reports feature.')
        ->call('save', true);

    $week = WeeklySummary::where('student_profile_id', $student->studentProfile->id)->sole();

    expect($week->status)->toBe(ActivityStatus::Submitted)
        ->and($week->submitted_at)->not->toBeNull();
});

test('a submitted week requires a summary', function () {
    $student = weeklySummarizer();

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->set('week_starts_on', '2026-10-05')
        ->set('summary', '')
        ->call('save', true)
        ->assertHasErrors('summary');
});

test('a student can edit and resubmit a returned week', function () {
    $student = weeklySummarizer();
    $week = createWeek($student, ['status' => ActivityStatus::Returned]);

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->call('edit', $week->id)
        ->assertSet('editingId', $week->id)
        ->call('save', true);

    expect($week->refresh()->status)->toBe(ActivityStatus::Submitted);
});

test('a published week cannot be edited or overwritten', function () {
    $student = weeklySummarizer();
    $week = createWeek($student, ['status' => ActivityStatus::Published]);

    $summaryBefore = $week->summary;

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->call('edit', $week->id)
        ->assertStatus(403);

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->set('week_starts_on', $week->week_starts_on->toDateString())
        ->set('summary', 'Hacked')
        ->call('save', false)
        ->assertHasErrors('week_starts_on');

    expect($week->refresh()->summary)->toBe($summaryBefore)
        ->and($week->status)->toBe(ActivityStatus::Published);
});

test('a student can submit a stored draft for review', function () {
    $student = weeklySummarizer();
    $week = createWeek($student, ['status' => ActivityStatus::Draft]);

    Livewire::actingAs($student)
        ->test('pages::student-weekly')
        ->call('submitWeek', $week->id);

    expect($week->refresh()->status)->toBe(ActivityStatus::Submitted);
});
