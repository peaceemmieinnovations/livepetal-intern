<?php

use App\Enums\CommentStatus;
use App\Enums\ContentStatus;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Livewire\Component;

new class extends Component
{
    public BlogPost $post;

    public string $body = '';

    public string $guestName = '';

    public string $guestEmail = '';

    public ?int $replyTo = null;

    public function mount(BlogPost $post): void
    {
        abort_unless(
            $post->status === ContentStatus::Published && $post->published_at !== null && $post->published_at <= now(),
            404,
        );

        $this->post = $post->load(['author', 'primaryCategory']);
        $this->post->incrementViews();
    }

    public function addComment(): void
    {
        $this->authorize('create', BlogComment::class);

        $user = auth()->user();

        $this->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
            'guestName' => $user ? ['nullable'] : ['required', 'string', 'min:2', 'max:100'],
            'guestEmail' => $user ? ['nullable'] : ['required', 'email', 'max:255'],
            'replyTo' => ['nullable', 'integer', 'exists:blog_comments,id,blog_post_id,'.$this->post->id],
        ]);

        $this->post->allComments()->create([
            'user_id' => $user?->id,
            'parent_id' => $this->replyTo,
            'guest_name' => $user ? null : $this->guestName,
            'guest_email' => $user ? null : $this->guestEmail,
            'body' => $this->body,
            'status' => $user ? CommentStatus::Published->value : CommentStatus::Pending->value,
        ]);

        $this->body = '';
        $this->guestName = '';
        $this->guestEmail = '';
        $this->replyTo = null;
    }

    public function render()
    {
        $comments = $this->post->comments()
            ->visible()
            ->with(['user', 'replies' => fn ($query) => $query->visible()->with('user')])
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->get();

        return $this->view([
            'user' => auth()->user(),
            'comments' => $comments,
        ])->layout('layouts::public')->title($this->post->title);
    }
};
?>

<div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('blog.index')" wire:navigate>{{ __('Insights') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('blog.show', $post)" wire:navigate>{{ $post->title }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <article>
        <header class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:badge variant="subtle" size="sm" inset="top bottom">
                    {{ $post->primaryCategory?->name ?? __('Uncategorised') }}
                </flux:badge>
                <span>
                    {{ $post->author?->name ?? 'Editorial' }} ·
                    {{ $post->published_at?->format('j M Y') }} ·
                    {{ __(':count reads', ['count' => $post->views_count]) }} ·
                    {{ __(':minutes min read', ['minutes' => $post->reading_minutes]) }}
                </span>
            </div>

            <flux:heading size="2xl" class="mt-4">{{ $post->title }}</flux:heading>

            @if ($post->excerpt)
                <p class="mt-3 text-base leading-7 text-zinc-500 dark:text-zinc-400">{{ $post->excerpt }}</p>
            @endif
        </header>

        <div class="prose-sm prose mt-8 max-w-none prose-zinc prose-headings:mt-8 prose-headings:font-semibold prose-p:text-zinc-700 prose-a:text-brand-600 dark:prose-invert dark:prose-p:text-zinc-300">
            {!! $post->body !!}
        </div>
    </article>

    <section class="mt-10 border-t border-zinc-200 pt-8 dark:border-zinc-800">
        <flux:heading size="lg">{{ __('Comments') }}</flux:heading>

        @if ($comments->isEmpty())
            <x-empty-state
                class="mt-4"
                :title="__('No comments yet')"
                :body="__('Start the conversation — everyone is welcome here.')"
            />
        @else
            <div class="mt-4 space-y-4">
                @foreach ($comments as $comment)
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $comment->authorName() }}</span>
                            · {{ $comment->created_at->diffForHumans() }}
                        </div>
                        <p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $comment->body }}</p>

                        @foreach ($comment->replies as $reply)
                            <div class="mt-3 ml-6 border-l border-zinc-200 pl-4 dark:border-zinc-700">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $reply->authorName() }}</span>
                                    · {{ $reply->created_at->diffForHumans() }}
                                </div>
                                <p class="mt-1 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $reply->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="addComment" class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            @if ($replyTo)
                <div class="mb-3 flex items-center justify-between gap-2 rounded-lg bg-zinc-100 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <span>{{ __('Replying to a comment') }}</span>
                    <flux:button wire:click="$set('replyTo', null)" variant="ghost" size="xs">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            @endif

            <flux:textarea wire:model="body" rows="3" placeholder="{{ __('Share your thoughts…') }}" />

            @guest
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <flux:input wire:model="guestName" label="{{ __('Your name') }}" />
                    <flux:input wire:model="guestEmail" label="{{ __('Your email') }}" hint="{{ __('Never shown publicly — only used for moderation.') }}" type="email" />
                </div>
            @endguest

            <div class="mt-4 flex items-center justify-between gap-3">
                @guest
                    <flux:text size="sm">{{ __('Guest comments appear after moderation by our team.') }}</flux:text>
                @else
                    <span></span>
                @endguest
                <flux:button type="submit" variant="primary" size="sm">
                    {{ __('Post comment') }}
                </flux:button>
            </div>
        </form>
    </section>
</div>