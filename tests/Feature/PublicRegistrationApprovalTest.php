<?php

use App\Enums\UserRole;
use App\Models\User;

test('gives a self-registered member public access without intern access', function () {
    $member = User::factory()->member()->create();

    expect($member->role)->toBe(UserRole::Member)
        ->and($member->isMember())->toBeTrue()
        ->and($member->isAwaitingApproval())->toBeTrue()
        ->and($member->isApproved())->toBeFalse()
        ->and($member->hasInternAccess())->toBeFalse();
});

test('does not grant intern access to an approved member who still has no intern role', function () {
    $member = User::factory()->member()->approved()->create();

    expect($member->isApproved())->toBeTrue()
        ->and($member->hasInternAccess())->toBeFalse()
        ->and($member->isAwaitingApproval())->toBeFalse();
});

test('grants intern access once an administrator approves and promotes the account', function () {
    $member = User::factory()->member()->create();

    $member->forceFill(['role' => UserRole::Student->value, 'approved_at' => now()])->save();
    $member->refresh();

    expect($member->hasInternAccess())->toBeTrue()
        ->and($member->isAwaitingApproval())->toBeFalse();
});

test('lists members awaiting approval oldest first', function () {
    $newest = User::factory()->pendingApproval()->create();
    $oldest = User::factory()->pendingApproval()->create(['applied_at' => now()->subDays(20)]);
    User::factory()->student()->create();

    expect(User::pendingApproval()->pluck('id')->all())
        ->toBe([$oldest->id, $newest->id]);
});

test('excludes approved members from the approval queue', function () {
    User::factory()->member()->approved()->create();

    expect(User::pendingApproval()->count())->toBe(0);
});

test('does not let a member reach trainee records before approval', function () {
    $student = User::factory()->student()->withStudentProfile()->create();
    $member = User::factory()->member()->create();

    expect($member->canAccessTraineeRecords($student->studentProfile))->toBeFalse()
        ->and($student->canAccessTraineeRecords($student->studentProfile))->toBeTrue();
});

test('does not offer a supervisor or administration dashboard to a member', function () {
    expect(User::factory()->member()->create()->dashboardRouteName())->toBe('member.dashboard')
        ->and(UserRole::Member->dashboardRoute())->toBe('member.dashboard');
});
