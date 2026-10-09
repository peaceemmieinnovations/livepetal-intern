<?php

use App\Enums\CommentStatus;
use App\Enums\CommunityPostType;
use App\Models\BlogPost;
use App\Models\CommunityPost;
use App\Models\User;
use Livewire\Livewire;

test('a guest is sent to sign in when replying or liking', function () {
    $post = CommunityPost::factory()->create();

    Livewire::test('pages::community-show', ['post' => $post])
        ->call('toggleLike')
        ->assertRedirect(route('login'));

    Livewire::test('pages::community-show', ['post' => $post])
        ->set('body', 'I would like to help.')
        ->call('addComment')
        ->assertRedirect(route('login'));
});

test('a member can publish a new community post', function () {
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::community-create')
        ->set('postType', CommunityPostType::Problem->value)
        ->set('body', 'I am stuck trying to wire up the password reset flow.')
        ->set('tagsText', 'laravel, auth')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('community.index'));

    $this->assertDatabaseHas('community_posts', [
        'user_id' => $user->id,
        'post_type' => CommunityPostType::Problem->value,
        'body' => 'I am stuck trying to wire up the password reset flow.',
        'status' => CommentStatus::Published->value,
    ]);
});

test('a community post requires a body of reasonable length', function () {
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::community-create')
        ->set('postType', CommunityPostType::Discussion->value)
        ->set('body', 'short')
        ->call('submit')
        ->assertHasErrors(['body']);
});

test('a member can like and then unlike a post', function () {
    $post = CommunityPost::factory()->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::community-show', ['post' => $post])
        ->call('toggleLike')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('community_post_likes', [
        'community_post_id' => $post->id,
        'user_id' => $user->id,
    ]);
    expect($post->refresh()->likes_count)->toBe(1);

    Livewire::test('pages::community-show', ['post' => $post])
        ->call('toggleLike');

    expect($post->refresh()->likes_count)->toBe(0);
    $this->assertDatabaseMissing('community_post_likes', [
        'community_post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

test('a member can reply to a post', function () {
    $post = CommunityPost::factory()->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::community-show', ['post' => $post])
        ->set('body', 'Happy to help you work through this.')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('community_comments', [
        'community_post_id' => $post->id,
        'user_id' => $user->id,
        'body' => 'Happy to help you work through this.',
        'status' => CommentStatus::Published->value,
    ]);
    expect($post->refresh()->comments_count)->toBe(1);
});

test('an author can mark their own problem solved', function () {
    $author = User::factory()->member()->create();
    $post = CommunityPost::factory()->problem()->create(['user_id' => $author->id]);
    $this->actingAs($author);

    Livewire::test('pages::community-show', ['post' => $post])
        ->call('markSolved')
        ->assertHasNoErrors();

    expect($post->refresh()->is_solved)->toBeTrue();
});

test('a member cannot solve a discussion post', function () {
    $author = User::factory()->member()->create();
    $post = CommunityPost::factory()->create(['user_id' => $author->id]);
    $this->actingAs($author);

    Livewire::test('pages::community-show', ['post' => $post])
        ->call('markSolved')
        ->assertForbidden();
});

test('a signed-in member can comment on an article immediately', function () {
    $post = BlogPost::factory()->create();
    $user = User::factory()->member()->create();
    $this->actingAs($user);

    Livewire::test('pages::blog-show', ['post' => $post])
        ->set('body', 'Thank you, this helped my placement report.')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('blog_comments', [
        'blog_post_id' => $post->id,
        'user_id' => $user->id,
        'body' => 'Thank you, this helped my placement report.',
        'status' => CommentStatus::Published->value,
    ]);
});

test('a guest comment on an article waits for moderation', function () {
    $post = BlogPost::factory()->create();

    Livewire::test('pages::blog-show', ['post' => $post])
        ->set('body', 'Useful read for a new intern!')
        ->set('guestName', 'Aisha Bello')
        ->set('guestEmail', 'aisha@example.com')
        ->call('addComment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('blog_comments', [
        'blog_post_id' => $post->id,
        'user_id' => null,
        'guest_name' => 'Aisha Bello',
        'body' => 'Useful read for a new intern!',
        'status' => CommentStatus::Pending->value,
    ]);
});
