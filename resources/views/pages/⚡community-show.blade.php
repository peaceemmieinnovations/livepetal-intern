<?php

use App\Enums\CommentStatus;
use App\Models\CommunityPost;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public CommunityPost $post;

    public string $body = '';

    public ?int $replyTo = null;

    public function mount(CommunityPost $post): void
    {
        abort_unless($post->status === CommentStatus::Published, 404);

        $this->post = $post->load('author');
    }

    public function toggleLike(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirect(route('login'));
            return;
        }

        $this->authorize('view', $this->post);

        DB::transaction(function () use ($user): void {
            $like = $this->post->likes()->where('user_id', $user->id)->first();

            if ($like) {
                $like->delete();
                $this->post->decrement('likes_count');
            } else {
                $this->post->likes()->create(['user_id' => $user->id]);
                $this->post->increment('likes_count');
            }
        });
    }

    public function markSolved(): void
    {
        $this->authorize('solve', $this->post);

        $this->post->forceFill([
            'is_solved' => true,
            'solved_by' => $this->post->user_id,
            'solved_at' => now(),
        ])->save();
    }

    public function markUnsolved(): void
    {
        $this->authorize('unsolve', $this->post);

        $this->post->forceFill(['is_solved' => false, 'solved_by' => null, 'solved_at' => null])->save();
    }

    public function addComment(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirect(route('login'));
            return;
        }

        $this->authorize('create', \App\Models\CommunityComment::class);

        $this->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
            'replyTo' => ['nullable', 'integer', 'exists:community_comments,id,community_post_id,'.$this->post->id],
        ]);

        $this->post->visibleComments()->create([
            'user_id' => $user->id,
            'parent_id' => $this->replyTo,
            'body' => $this->body,
            'status' => CommentStatus::Published->value,
        ]);

        $this->post->increment('comments_count');
        $this->body = '';
        $this->replyTo = null;
    }

    public function render()
    {
        $user = auth()->user();

        $comments = $this->post->visibleComments()
            ->with(['author', 'replies' => fn ($query) => $query->visible()->with('author')])
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->get();

        return $this->view([
            'user' => $user,
            'liked' => $this->post->likedBy($user),
            'comments' => $comments,
        ])->layout('layouts::public')->title(__('Community post'));
    }
};
?>

<div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('community.index')" wire:navigate>{{ __('Community') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('community.show', $post)" wire:navigate>{{ __('Post') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <article class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
            @if ($post->is_pinned)
                <flux:badge variant="warning" size="sm" inset="top bottom">{{ __('Pinned') }}</flux:badge>
            @endif
            <flux:badge variant="subtle" size="sm" inset="top bottom">{{ $post->post_type->label() }}</flux:badge>
            @if ($post->isAnswerable() && $post->is_solved)
                <flux:badge variant="success" size="sm" inset="top bottom">{{ __('Solved') }}</flux:badge>
            @endif
            <span>
                {{ $post->authorName() }} · {{ $post->authorRole() }} ·
                {{ $post->created_at->format('j M Y') }}
            </span>
        </div>

        <div class="mt-4 text-sm leading-7 text-zinc-700 dark:text-zinc-200">
            {!! nl2br(e($post->body)) !!}
        </div>

        @if ($post->tags)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <flux:badge variant="subtle" size="sm" inset="top bottom">#{{ $tag }}</flux:badge>
                @endforeach
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
            <flux:button
                wire:click="toggleLike"
                :variant="$liked ? 'primary' : 'ghost'"
                size="sm"
            >
                {{ $liked ? __('Liked (:count)', ['count' => $post->likes_count]) : __('Like (:count)', ['count' => $post->likes_count]) }}
            </flux:button>

            @if ($post->isAnswerable())
                @if ($post->is_solved)
                    @can('unsolve', $post)
                        <flux:button wire:click="markUnsolved" variant="ghost" size="sm">
                            {{ __('Mark unsolved') }}
                        </flux:button>
                    @endcan
                @else
                    @can('solve', $post)
                        <flux:button wire:click="markSolved" variant="primary" size="sm" icon="check-circle">
                            {{ __('Mark as solved') }}
                        </flux:button>
                    @endcan
                @endif
            @endif

            <flux:text class="ml-auto">{{ __(':count replies', ['count' => $post->comments_count]) }}</flux:text>
        </div>
    </article>

    <section class="mt-8">
        <flux:heading size="lg">{{ __('Replies') }}</flux:heading>

        @if ($comments->isEmpty())
            <x-empty-state
                class="mt-4"
                :title="__('No replies yet')"
                :body="auth()->check()
                    ? __('Be the first to reply or offer help to the author.')
                    : __('Sign in to join the conversation.')"
            />
        @else
            <div class="mt-4 space-y-4">
                @foreach ($comments as $comment)
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $comment->author->name }}</span>
                            · {{ $comment->created_at->diffForHumans() }}
                        </div>
                        <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $comment->body }}</p>

                        @auth
                            <flux:button
                                wire:click="$set('replyTo', {{ $comment->id }})"
                                variant="ghost"
                                size="xs"
                                class="mt-2"
                            >
                                {{ __('Reply') }}
                            </flux:button>
                        @endauth

                        @foreach ($comment->replies as $reply)
                            <div class="mt-3 ml-6 border-l border-zinc-200 pl-4 dark:border-zinc-700">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $reply->author->name }}</span>
                                    · {{ $reply->created_at->diffForHumans() }}
                                </div>
                                <p class="mt-1 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $reply->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        @guest
            <div class="mt-6 rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                <flux:text>
                    {{ __('Join the conversation — locate your live view and start helping your peers.') }}
                </flux:text>
                <flux:button :href="route('login')" wire:navigate variant="primary" class="mt-4">
                    {{ __('Sign in to reply') }}
                </flux:button>
            </div>
        @else
            <form wire:submit="addComment" class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                @if ($replyTo)
                    <div class="mb-3 flex items-center justify-between gap-2 rounded-lg bg-zinc-100 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <span>{{ __('Replying to a comment') }}</span>
                        <flux:button wire:click="$set('replyTo', null)" variant="ghost" size="xs">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                @endif

                <flux:textarea wire:model="body" rows="3" placeholder="{{ __('Write a helpful reply…') }}" />

                <div class="mt-3 flex justify-end">
                    <flux:button type="submit" variant="primary" size="sm">
                        {{ __('Post reply') }}
                    </flux:button>
                </div>
            </form>
        @endguest
    </section>
</div>