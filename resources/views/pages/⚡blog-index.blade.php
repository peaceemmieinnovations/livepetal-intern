<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $category = '';

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->published()
            ->with(['author', 'primaryCategory'])
            ->when(filled($this->category), function ($query) {
                $query->whereHas('primaryCategory', fn ($category) => $category->where('slug', $this->category));
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(9);

        return $this->view([
            'posts' => $posts,
            'categories' => BlogCategory::query()
                ->withCount('posts')
                ->orderByDesc('posts_count')
                ->get(),
        ])->layout('layouts::public')->title(__('Insights'));
    }
};
?>

<div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <header class="max-w-2xl">
        <flux:heading size="2xl">{{ __('Insights') }}</flux:heading>
        <flux:text class="mt-2 block">
            {{ __('Practical guides, campus stories and behind-the-scenes notes for trainees and anyone curious about SIWES.') }}
        </flux:text>
    </header>

    <div class="mt-8 flex flex-wrap gap-2">
        <flux:button wire:click="$set('category', '')" :variant="$category === '' ? 'primary' : 'ghost'" size="sm">
            {{ __('All articles') }}
        </flux:button>

        @foreach ($categories as $blogCategory)
            <flux:button
                wire:click="$set('category', '{{ $blogCategory->slug }}')"
                :variant="$category === $blogCategory->slug ? 'primary' : 'ghost'"
                size="sm"
            >
                {{ $blogCategory->name }}
            </flux:button>
        @endforeach
    </div>

    @if ($posts->isEmpty())
        <x-empty-state
            class="mt-10"
            :title="__('No published articles yet')"
            :body="__('Check back soon — the editorial team is writing.')"
        />
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    @if ($post->is_featured)
                        <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-1.5 text-center text-xs font-semibold uppercase tracking-wider text-brand-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-brand-500">
                            {{ __('Featured') }}
                        </div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center justify-between gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $post->primaryCategory?->name ?? __('Uncategorised') }}</span>
                            <span>{{ __(':minutes min read', ['minutes' => $post->reading_minutes]) }}</span>
                        </div>

                        <h2 class="mt-3 text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="{{ route('blog.show', $post) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-500">
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if ($post->excerpt)
                            <p class="mt-2 flex-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                {{ $post->excerpt }}
                            </p>
                        @endif

                        <div class="mt-4 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $post->author?->name ?? 'Editorial' }}</span>
                            <span>·</span>
                            <span>{{ $post->published_at?->format('j M Y') }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    @endif
</div>