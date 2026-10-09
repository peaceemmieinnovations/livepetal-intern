<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Livewire\Livewire;

test('a guest is sent to sign in when starting a course', function () {
    $course = Course::factory()->withModules(2)->create();

    Livewire::test('pages::courses-show', ['course' => $course])
        ->call('enroll')
        ->assertRedirect(route('login'));
});

test('a signed-in member can start a course', function () {
    $course = Course::factory()->withModules(2)->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::courses-show', ['course' => $course])
        ->call('enroll')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('course_enrollments', [
        'course_id' => $course->id,
        'user_id' => $user->id,
    ]);
});

test('starting a course twice does not duplicate the enrolment', function () {
    $course = Course::factory()->withModules(2)->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::courses-show', ['course' => $course])->call('enroll');

    Livewire::test('pages::courses-show', ['course' => $course])->call('enroll');

    expect($course->enrollments()->where('user_id', $user->id)->count())->toBe(1);
});

test('a member can complete a module from the course page', function () {
    $course = Course::factory()->withModules(2)->create();
    $user = User::factory()->member()->create();
    $enrollment = CourseEnrollment::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
    ]);
    $this->actingAs($user);

    $firstModule = $course->publishedModules()->orderBy('sort_order')->first();

    Livewire::test('pages::courses-show', ['course' => $course])
        ->call('completeModule', $firstModule->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('course_module_completions', [
        'course_enrollment_id' => $enrollment->id,
        'course_module_id' => $firstModule->id,
    ]);

    expect($enrollment->refresh()->progress_percent)->toBe(50);
});

test('a member cannot mark complete a module in a course they are not enrolled in', function () {
    $course = Course::factory()->withModules(2)->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    $firstModule = $course->publishedModules()->orderBy('sort_order')->first();

    Livewire::test('pages::courses-show', ['course' => $course])
        ->call('completeModule', $firstModule->id)
        ->assertForbidden();
});
