@props([
    'title',
    'body',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center dark:border-zinc-600 dark:bg-zinc-900/60']) }}>
    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $title }}</p>
    <p class="mx-auto mt-2 max-w-prose text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $body }}</p>

    @if (filled($actionLabel) && filled($actionHref))
        <flux:button href="{{ $actionHref }}" variant="primary" class="mt-5" wire:navigate>
            {{ $actionLabel }}
        </flux:button>
    @endif
</div>
