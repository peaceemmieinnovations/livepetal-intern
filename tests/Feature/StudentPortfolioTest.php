<?php

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

function portfolioOwner(): User
{
    return User::factory()->student()->withStudentProfile()->create();
}

test('a student can open their portfolio settings', function () {
    $student = portfolioOwner();

    $this->actingAs($student)
        ->get(route('student.portfolio'))
        ->assertOk()
        ->assertSee('Public portfolio');
});

test('roles without student intern access cannot open portfolio settings', function () {
    foreach ([
        User::factory()->member()->create(),
        User::factory()->admin()->create(),
        User::factory()->supervisor()->approved()->create(),
    ] as $user) {
        $this->actingAs($user)->get(route('student.portfolio'))->assertForbidden();
    }
});

test('a student can make their portfolio public', function () {
    $student = portfolioOwner();

    Livewire::actingAs($student)
        ->test('pages::student-portfolio')
        ->set('is_public', true)
        ->set('show_bio', true)
        ->set('show_skills', true)
        ->call('save');

    $profile = $student->studentProfile->refresh();

    expect($profile->is_public)->toBeTrue()
        ->and($profile->show_bio)->toBeTrue()
        ->and($profile->show_skills)->toBeTrue();
});

test('the public portfolio shows public projects and hides private ones', function () {
    $student = portfolioOwner();

    $profile = $student->studentProfile;
    $profile->update(['is_public' => true, 'approved_at' => now(), 'show_projects' => true, 'show_bio' => true, 'bio' => 'Full stack trainee.']);

    Project::factory()->portfolioReady()->create(['student_profile_id' => $profile->id, 'name' => 'Public SIWES Tracker']);
    Project::factory()->create(['student_profile_id' => $profile->id, 'name' => 'Secret Lab Tool', 'visibility' => Visibility::Private->value]);

    $this->get(route('students.show', $student->username))
        ->assertOk()
        ->assertSee($student->name)
        ->assertSee('Public SIWES Tracker')
        ->assertDontSee('Secret Lab Tool');
});

test('a private or unapproved profile is not publicly reachable', function () {
    $student = portfolioOwner();
    Project::factory()->create(['student_profile_id' => $student->studentProfile->id, 'name' => 'Private project']);

    $this->get(route('students.show', $student->username))
        ->assertNotFound();
});

test('an unknown username gets a 404', function () {
    $this->get(route('students.show', 'does-not-exist'))
        ->assertNotFound();
});

test('the public portfolio honours the bio and academic toggles', function () {
    $student = portfolioOwner();
    $profile = $student->studentProfile;
    $profile->update(['is_public' => true, 'approved_at' => now(), 'show_bio' => true, 'bio' => 'Laravel engineering intern.', 'show_academic' => false]);

    $this->get(route('students.show', $student->username))
        ->assertSee('Laravel engineering intern.');
});
