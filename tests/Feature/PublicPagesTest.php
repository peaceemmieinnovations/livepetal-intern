<?php

use App\Enums\CommentStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CommunityPost;
use App\Models\Course;

test('the home page renders a landing experience for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(__('Create a free account'));
});

test('the home page features published courses', function () {
    $course = Course::factory()->create(['title' => 'Landing Featured Course']);
    Course::factory()->draft()->create(['title' => 'Draft Course Not Shown']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Landing Featured Course')
        ->assertDontSee('Draft Course Not Shown');
});

test('a guest can browse the course catalogue', function () {
    $this->get(route('courses.index'))
        ->assertOk()
        ->assertSee(__('Free courses'));
});

test('a guest can open a published course', function () {
    $course = Course::factory()->create();

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee($course->title);
});

test('a draft course is not routed to the public', function () {
    $course = Course::factory()->draft()->create();

    $this->get(route('courses.show', $course))->assertNotFound();
});

test('a guest can read the community feed', function () {
    CommunityPost::factory()->create(['body' => 'A public community update.']);

    $this->get(route('community.index'))
        ->assertOk()
        ->assertSee(__('Community'))
        ->assertSee('A public community update.');
});

test('the community feed hides pending posts', function () {
    CommunityPost::factory()->create([
        'status' => CommentStatus::Pending->value,
        'body' => 'Quietly waiting for review.',
    ]);

    $this->get(route('community.index'))
        ->assertOk()
        ->assertDontSee('Quietly waiting for review.');
});

test('a pending post is not routed to the public', function () {
    $post = CommunityPost::factory()->create(['status' => CommentStatus::Pending->value]);

    $this->get(route('community.show', $post))->assertNotFound();
});

test('a guest is redirected to sign in before creating a community post', function () {
    $this->get(route('community.create'))->assertRedirect(route('login'));
});

test('a guest can read a published article', function () {
    $post = BlogPost::factory()->create();

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertSee($post->title);
});

test('a guest can browse the insights listing', function () {
    BlogPost::factory()->create(['title' => 'Insights Listing Post']);
    BlogCategory::factory()->create(['slug' => 'listed-category']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('Insights Listing Post');
});

test('a draft article is not routed to the public', function () {
    $post = BlogPost::factory()->draft()->create();

    $this->get(route('blog.show', $post))->assertNotFound();
});

test('a guest can open the products page and the hire page', function () {
    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee(__('Thoughtful tools for work that matters.'));

    $this->get(route('hire'))
        ->assertOk()
        ->assertSee(__('Bring practical builders onto your team.'));
});

test('a guest can open the services page', function () {
    $this->get(route('services.index'))
        ->assertOk()
        ->assertSee(__('Services that make work honest.'))
        ->assertSee(__('Vetted Talent Hire'));
});

test('the public navigation exposes services and the hire dropdown', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('services.index'), false)
        ->assertSee('Hire an Expert')
        ->assertSee('/hire#enquire');
});
