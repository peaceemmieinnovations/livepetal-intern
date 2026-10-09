@props([
    'label',
    'value',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900']) }}>
    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $value }}</p>

    @if (filled($hint))
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>
    @endif
</div>
