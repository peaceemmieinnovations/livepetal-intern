<?php

use App\Enums\ContentStatus;
use App\Models\JobPosting;

test('keeps the listing published while applications are paused', function () {
    $posting = JobPosting::factory()->applicationsPaused()->create();

    expect($posting->status)->toBe(ContentStatus::Published)
        ->and($posting->isOpen())->toBeTrue();
});

test('hides applications when an administrator pauses them', function () {
    $posting = JobPosting::factory()->applicationsPaused()->create();

    expect($posting->isAcceptingApplications())->toBeFalse()
        ->and($posting->applicationsClosedReason())->toBe('Applications are temporarily paused. Check back shortly.');
});

test('accepts applications when nothing is paused', function () {
    $posting = JobPosting::factory()->create();

    expect($posting->isAcceptingApplications())->toBeTrue()
        ->and($posting->applicationsClosedReason())->toBeNull();
});

test('blocks applications before the window opens', function () {
    $posting = JobPosting::factory()->create([
        'applications_open_at' => now()->addWeek(),
        'applications_close_at' => now()->addWeeks(3),
    ]);

    expect($posting->isAcceptingApplications())->toBeFalse()
        ->and($posting->applicationsClosedReason())
        ->toBe('Applications open on '.now()->addWeek()->format('j F Y').'.');
});

test('accepts applications inside the window', function () {
    $posting = JobPosting::factory()->create([
        'applications_open_at' => now()->subDay(),
        'applications_close_at' => now()->addWeeks(3),
    ]);

    expect($posting->isAcceptingApplications())->toBeTrue();
});

test('blocks applications once the window closes', function () {
    $posting = JobPosting::factory()->create([
        'applications_open_at' => now()->subMonths(2),
        'applications_close_at' => now()->subDay(),
    ]);

    expect($posting->isAcceptingApplications())->toBeFalse()
        ->and($posting->applicationsClosedReason())
        ->toBe('Applications closed on '.now()->subDay()->format('j F Y').'.');
});

test('blocks applications after the deadline even when the switch is on', function () {
    $posting = JobPosting::factory()->create(['deadline' => now()->subDay()]);

    expect($posting->isAcceptingApplications())->toBeFalse()
        ->and($posting->applicationsClosedReason())
        ->toBe('The application deadline for this opportunity has passed.');
});

test('excludes listings that are not accepting applications from the accepting scope', function () {
    JobPosting::factory()->applicationsPaused()->create();
    $open = JobPosting::factory()->create();

    $accepted = JobPosting::query()->acceptingApplications()->pluck('id')->all();

    expect($accepted)->toBe([$open->id]);
});
