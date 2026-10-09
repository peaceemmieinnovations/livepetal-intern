<?php

use App\Enums\UserRole;
use App\Models\User;

test('a guest can open the registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee(__('Create your account'));
});

test('a visitor can create a member account awaiting approval', function () {
    $response = $this->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'application_note' => 'I want to build my portfolio.',
    ]);

    $response->assertRedirect();

    $member = User::where('email', 'jane@example.com')->firstOrFail();

    expect($member->role)->toBe(UserRole::Member)
        ->and($member->isMember())->toBeTrue()
        ->and($member->isAwaitingApproval())->toBeTrue()
        ->and($member->isApproved())->toBeFalse()
        ->and($member->hasInternAccess())->toBeFalse()
        ->and($member->applied_at)->not->toBeNull()
        ->and($member->application_note)->toBe('I want to build my portfolio.');
});

test('registration rejects a duplicate email', function () {
    User::factory()->create(['email' => 'jamie@example.com']);

    $this->post(route('register'), [
        'name' => 'Jamie Doe',
        'email' => 'jamie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});

test('registration requires matching password confirmation', function () {
    $this->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');
});

test('an authentication attempt works for a newly registered member', function () {
    $this->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => 'jane@example.com',
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs(User::where('email', 'jane@example.com')->first());
});
