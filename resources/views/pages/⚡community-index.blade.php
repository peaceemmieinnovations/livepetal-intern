<?php

use App\Enums\CommunityPostType;
use App\Models\CommunityPost;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $filter = '';

    public string $search = '';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $posts = CommunityPost::query()
            ->published()
            ->with('author')
            ->when(filled($this->filter), fn ($query) => $query->where('post_type', $this->filter))
            ->when(filled($this->search), fn ($query) => $query->where('body', 'like', '%'.$this->search.'%'))
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(12);

        return $this->view([
            'posts' => $posts,
            'types' => CommunityPostType::cases(),
        ])->layout('layouts::public')->title(__('Community'));
    }
};
?>

<div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            <flux:heading size="2xl">{{ __('Community') }}</flux:heading>
            <flux:text class="mt-2 block">
                {{ __('Trainees help each other, share builds and answer problems here. Every contribution stays honest and attributed.') }}
            </flux:text>
        </div>

        @auth
            <flux:button variant="primary" :href="route('community.create')" wire:navigate>
                {{ __('New post') }}
            </flux:button>
        @endauth
    </header>

    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search posts…') }}"
            class="sm:max-w-sm"
        />

        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="$set('filter', '')" :variant="$filter === '' ? 'primary' : 'ghost'" size="sm">
                {{ __('All') }}
            </flux:button>

            @foreach ($types as $type)
                <flux:button wire:click="$set('filter', '{{ $type->value }}')" :variant="$filter === $type->value ? 'primary' : 'ghost'" size="sm">
                    {{ $type->label() }}
                </flux:button>
            @endforeach
        </div>
    </div>

    @if ($posts->isEmpty())
        <x-empty-state
            class="mt-10"
            :title="__('No community posts yet')"
            :body="auth()->check()
                ? __('Be the first to share what you are building or ask for help.')
                : __('Once the first member posts, the feed will appear here.')"
        />
    @else
        <div class="mt-8 space-y-4">
            @foreach ($posts as $post)
                <article class="rounded-xl border border-zinc-200 bg-white p-6 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
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
                                {{ $post->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('community.show', $post) }}" wire:navigate class="mt-3 block text-sm leading-6 text-zinc-700 line-clamp-3 dark:text-zinc-200 hover:text-brand-600 dark:hover:text-brand-500">
                        {{ $post->body }}
                    </a>

                    <div class="mt-4 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="hand-thumb-up" class="size-4" />
                            {{ $post->likes_count }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="chat-bubble-left-ellipsis" class="size-4" />
                            {{ $post->comments_count }}
                        </span>
                        <span class="ml-auto">
                            <a href="{{ route('community.show', $post) }}" wire:navigate class="font-medium text-brand-600 hover:text-brand-500 dark:text-brand-500 dark:hover:text-brand-400">
                                {{ __('View post') }}
                            </a>
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    @endif
</div>