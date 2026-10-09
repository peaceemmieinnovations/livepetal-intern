<?php

use App\Actions\ApproveApplicant;
use App\Enums\UserRole;
use App\Models\User;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('an administrator can open the membership approvals page', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->pendingApproval()->create();

    $this->actingAs($admin)
        ->get(route('admin.approvals'))
        ->assertOk()
        ->assertSee($member->name)
        ->assertSee($member->application_note);
});

test('a super administrator can open the membership approvals page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.approvals'))
        ->assertOk();
});

test('non-administrative roles cannot open the membership approvals page', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->student()->create(),
        User::factory()->supervisor()->approved()->create(),
    ] as $user) {
        $this->actingAs($user)
            ->get(route('admin.approvals'))
            ->assertForbidden();
    }
});

test('an administrator can approve a member from the page', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin-approvals')
        ->call('approve', $member->id);

    $member->refresh();

    expect($member->role)->toBe(UserRole::Student)
        ->and($member->isApproved())->toBeTrue()
        ->and($member->approved_by)->toBe($admin->id)
        ->and($member->hasInternAccess())->toBeTrue();
});

test('approving an applicant removes them from the approval queue', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin-approvals')
        ->call('approve', $member->id);

    expect(User::pendingApproval()->count())->toBe(0);
});

test('an administrator can reject a member with a reason', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin-approvals')
        ->call('beginReject', $member->id)
        ->set('rejectNote', 'Requires an enrolled institution.')
        ->call('reject');

    $member->refresh();

    expect($member->isRejected())->toBeTrue()
        ->and($member->rejected_by)->toBe($admin->id)
        ->and($member->decision_note)->toBe('Requires an enrolled institution.')
        ->and($member->role)->toBe(UserRole::Member)
        ->and($member->isMember())->toBeTrue()
        ->and($member->isAwaitingApproval())->toBeFalse();
});

test('rejecting an applicant requires a reason', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin-approvals')
        ->call('beginReject', $member->id)
        ->set('rejectNote', '')
        ->call('reject')
        ->assertHasErrors(['rejectNote' => 'required']);
});

test('cancelling the reject form leaves the applicant untouched', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin-approvals')
        ->call('beginReject', $member->id)
        ->set('rejectNote', 'A reason.')
        ->call('cancelReject');

    $member->refresh();

    expect($member->rejected_at)->toBeNull()
        ->and(User::pendingApproval()->count())->toBe(1);
});

test('rejected members leave the approval queue but keep public member access', function () {
    $rejected = User::factory()->member()->rejected()->create();
    $member = User::factory()->member()->create();

    expect(User::pendingApproval()->pluck('id')->all())->toBe([$member->id])
        ->and($rejected->isRejected())->toBeTrue()
        ->and($rejected->hasInternAccess())->toBeFalse();
});

test('the approve action refuses applicants that are not awaiting approval', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();

    expect(fn () => app(ApproveApplicant::class)->execute($student, $admin))
        ->toThrow(HttpException::class);
});
