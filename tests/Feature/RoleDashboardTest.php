<?php

use App\Models\User;

test('a member can visit their member dashboard', function () {
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    $this->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee(__('Welcome back, :name', ['name' => $user->name]));
});

test('an approved student can visit their trainee dashboard', function () {
    $user = User::factory()->student()->create();
    $this->actingAs($user);

    $this->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee(__('Trainee dashboard'));
});

test('an approved supervisor can visit their supervisor dashboard', function () {
    $user = User::factory()->supervisor()->approved()->create();
    $this->actingAs($user);

    $this->get(route('supervisor.dashboard'))
        ->assertOk()
        ->assertSee(__('Supervisor dashboard'));
});

test('an administrator can visit the administration console', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('Administration'));
});

test('a super administrator can visit the administration console', function () {
    $user = User::factory()->superAdmin()->create();
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))
        ->assertOk();
});

test('a member cannot open intern or administration dashboards', function () {
    $member = User::factory()->member()->create();
    $this->actingAs($member);

    $this->get(route('student.dashboard'))->assertForbidden();
    $this->get(route('supervisor.dashboard'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('a student cannot open the administration console', function () {
    $student = User::factory()->student()->create();
    $this->actingAs($student);

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('an unapproved supervisor cannot reach their supervisor dashboard', function () {
    $supervisor = User::factory()->supervisor()->create();
    $this->actingAs($supervisor);

    $this->get(route('supervisor.dashboard'))->assertForbidden();
});

test('a supervisor cannot open intern dashboards assigned to other roles', function () {
    $supervisor = User::factory()->supervisor()->approved()->create();
    $this->actingAs($supervisor);

    $this->get(route('student.dashboard'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
});
