<?php

use App\Actions\CompleteCourseModule;
use App\Actions\IssueCourseCertificate;
use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Progress and certificate issuance share one flow, so they are asserted
 * together: completing modules is what makes a certificate possible.
 */
test('starts an enrolment at zero percent', function () {
    $enrollment = CourseEnrollment::factory()->create();

    expect($enrollment->progress_percent)->toBe(0)
        ->and($enrollment->isComplete())->toBeFalse();
});

test('counts published modules only when calculating progress', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $modules = CourseModule::factory()->count(2)->create([
        'course_id' => $enrollment->course_id,
        'sort_order' => 1,
    ]);
    CourseModule::factory()->draft()->create([
        'course_id' => $enrollment->course_id,
        'sort_order' => 3,
    ]);

    $modules->each(fn (CourseModule $module) => app(CompleteCourseModule::class)->execute($module, $enrollment));

    expect($enrollment->refresh()->progress_percent)->toBe(100)
        ->and($enrollment->isComplete())->toBeTrue();
});

test('keeps progress below one hundred until every module is finished', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $modules = CourseModule::factory()->count(4)->create([
        'course_id' => $enrollment->course_id,
        'sort_order' => 1,
    ]);

    $modules->take(3)->each(fn (CourseModule $module) => app(CompleteCourseModule::class)->execute($module, $enrollment));

    expect($enrollment->refresh()->progress_percent)->toBe(75)
        ->and($enrollment->isComplete())->toBeFalse()
        ->and($enrollment->certificate())->toBeNull();
});

test('issues a certificate when the last module is finished', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $modules = CourseModule::factory()->count(2)->create([
        'course_id' => $enrollment->course_id,
        'sort_order' => 1,
    ]);

    $modules->each(fn (CourseModule $module) => app(CompleteCourseModule::class)->execute($module, $enrollment));

    $certificate = $enrollment->refresh()->certificate();

    expect($certificate)->not->toBeNull()
        ->and($certificate->recipient_name)->toBe($enrollment->user->name)
        ->and($certificate->serial_number)->not->toBeEmpty()
        ->and($certificate->uuid)->not->toBeEmpty()
        ->and($enrollment->course->certificateFor($enrollment->user)->id)->toBe($certificate->id);
});

test('does not issue a certificate before the course is finished', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $enrollment->course_id]);

    expect(app(IssueCourseCertificate::class)->execute($enrollment))->toBeNull()
        ->and($enrollment->certificate())->toBeNull();
});

test('repeating completion does not inflate progress or issue a second certificate', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $enrollment->course_id]);

    app(CompleteCourseModule::class)->execute($module, $enrollment);
    app(CompleteCourseModule::class)->execute($module, $enrollment);

    expect($enrollment->refresh()->progress_percent)->toBe(100)
        ->and($enrollment->completions()->count())->toBe(1)
        ->and(CourseCertificate::query()->where('course_id', $enrollment->course_id)->count())->toBe(1);
});

test('refuses to complete a module that is not published', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $module = CourseModule::factory()->draft()->create(['course_id' => $enrollment->course_id]);

    app(CompleteCourseModule::class)->execute($module, $enrollment);
})->throws(ModelNotFoundException::class);

test('treats a module as complete only for the matching enrolment', function () {
    $enrollment = CourseEnrollment::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $enrollment->course_id]);
    $otherEnrollment = CourseEnrollment::factory()->create(['course_id' => $enrollment->course_id]);

    app(CompleteCourseModule::class)->execute($module, $enrollment);

    expect($module->isCompletedBy($enrollment->refresh()))->toBeTrue()
        ->and($module->isCompletedBy($otherEnrollment->refresh()))->toBeFalse()
        ->and($module->isCompletedBy(null))->toBeFalse();
});
