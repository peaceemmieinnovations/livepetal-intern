<?php

use App\Enums\CommentStatus;
use App\Enums\CommunityPostType;
use App\Models\CommunityPost;
use App\Models\User;

test('includes only problems and questions', function () {
    $problem = CommunityPost::factory()->problem()->create();
    $question = CommunityPost::factory()->create(['post_type' => CommunityPostType::Question->value]);
    CommunityPost::factory()->create(['post_type' => CommunityPostType::Discussion->value]);
    CommunityPost::factory()->create(['post_type' => CommunityPostType::Showcase->value]);

    expect(CommunityPost::query()->problems()->pluck('id')->sort()->values()->all())
        ->toBe([$problem->id, $question->id]);
});

test('lists unsolved problems before solved ones', function () {
    $solved = CommunityPost::factory()->solved(User::factory()->student()->create())
        ->create(['created_at' => now()->subWeeks(2)]);
    $unsolvedNew = CommunityPost::factory()->problem()->create(['created_at' => now()->subDay()]);
    $unsolvedOld = CommunityPost::factory()->problem()->create(['created_at' => now()->subWeeks(3)]);

    $order = CommunityPost::query()->problems()->pluck('id')->all();

    expect($order)->toBe([$unsolvedOld->id, $unsolvedNew->id, $solved->id]);
});

test('excludes hidden and draft posts', function () {
    CommunityPost::factory()->problem()->create();
    CommunityPost::factory()->problem()->create(['status' => CommentStatus::Pending->value]);

    expect(CommunityPost::query()->problems()->count())->toBe(1);
});

test('restricts the unsolved scope to open problems', function () {
    CommunityPost::factory()->problem()->create();
    CommunityPost::factory()->solved(User::factory()->student()->create())->create();

    expect(CommunityPost::query()->problems()->unsolved()->count())->toBe(1);
});

test('reports whether a post expects an accepted answer', function () {
    $problem = CommunityPost::factory()->problem()->make();
    $discussion = CommunityPost::factory()->make();

    expect($problem->isAnswerable())->toBeTrue()
        ->and($discussion->isAnswerable())->toBeFalse();
});
