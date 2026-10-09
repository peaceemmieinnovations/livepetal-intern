@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="flex flex-col min-h-screen">
            @php
                $publicNav = [
                    ['label' => __('Courses'), 'route' => 'courses.index'],
                    ['label' => __('Community'), 'route' => 'community.index'],
                    ['label' => __('Insights'), 'route' => 'blog.index'],
                    ['label' => __('Products'), 'route' => 'products.index'],
                    ['label' => __('Services'), 'route' => 'services.index'],
                    [
                        'label' => __('Hire an Expert'),
                        'route' => 'hire',
                        'children' => [
                            ['label' => __('Hire an intern or graduate'), 'href' => route('hire').'#enquire'],
                            ['label' => __('Build a product with Livepetal'), 'href' => route('hire').'#enquire'],
                            ['label' => __('Get engineering consulting'), 'href' => route('hire').'#enquire'],
                            ['label' => __('Partner on a training programme'), 'href' => route('hire').'#enquire'],
                        ],
                    ],
                ];
            @endphp

            <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/80">
                <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" wire:navigate class="flex min-w-0 items-center gap-2.5 text-sm font-semibold">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-brand-600 text-white">
                            <x-app-logo-icon class="size-5 fill-current" />
                        </span>
                        <span class="truncate">{{ config('app.name', 'Livepetal') }}</span>
                    </a>

                    <nav class="hidden items-center gap-6 text-sm font-medium text-zinc-600 lg:flex dark:text-zinc-300">
                        @foreach (collect($publicNav)->filter(fn (array $item): bool => Route::has($item['route'])) as $item)
                            @if (! empty($item['children']))
                                <flux:dropdown>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-trailing="chevron-down"
                                        class="text-sm font-medium {{ request()->routeIs($item['route']) ? 'text-brand-600 dark:text-brand-500' : 'text-zinc-600 dark:text-zinc-300' }}"
                                    >
                                        {{ $item['label'] }}
                                    </flux:button>
                                    <flux:menu>
                                        @foreach ($item['children'] as $child)
                                            <flux:menu.item :href="$child['href']" wire:navigate>{{ $child['label'] }}</flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <a
                                    href="{{ route($item['route']) }}"
                                    wire:navigate
                                    class="{{ request()->routeIs($item['route']) ? 'text-brand-600 dark:text-brand-500' : 'transition hover:text-zinc-900 dark:hover:text-white' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </nav>

                    <div class="flex shrink-0 items-center gap-2">
                        @auth
                            <flux:button
                                :href="route(auth()->user()->dashboardRouteName())"
                                variant="primary"
                                size="sm"
                                wire:navigate
                            >
                                {{ __('Dashboard') }}
                            </flux:button>
                        @else
                            <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>
                                {{ __('Sign in') }}
                            </flux:button>
                            @if (Route::has('register'))
                                <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>
                                    {{ __('Create account') }}
                                </flux:button>
                            @endif
                        @endauth
                    </div>
                </div>

                <nav class="flex items-center gap-5 overflow-x-auto border-t border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-600 lg:hidden dark:border-zinc-800 dark:text-zinc-300">
                    @foreach (collect($publicNav)->filter(fn (array $item): bool => Route::has($item['route'])) as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            class="whitespace-nowrap pb-0.5 {{ request()->routeIs($item['route']) ? 'border-b border-brand-600 text-brand-600 dark:border-brand-500 dark:text-brand-500' : 'hover:text-zinc-900 dark:hover:text-white' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                        @foreach ($item['children'] ?? [] as $child)
                            <a
                                href="{{ $child['href'] }}"
                                wire:navigate
                                class="whitespace-nowrap pb-0.5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                            >
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    @endforeach
                </nav>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-zinc-200 dark:border-zinc-800">
                <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 text-sm font-semibold">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-brand-600 text-white">
                                <x-app-logo-icon class="size-5 fill-current" />
                            </span>
                            <span>{{ config('app.name', 'Livepetal') }}</span>
                        </a>
                        <p class="mt-4 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            {{ __('Structured SIWES placement, honest documentation and real portfolios for Livepetal Systems Limited trainees.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Learn') }}</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            @if (Route::has('courses.index'))
                                <li><a href="{{ route('courses.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Free courses') }}</a></li>
                            @endif
                            @if (Route::has('blog.index'))
                                <li><a href="{{ route('blog.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Insights') }}</a></li>
                            @endif
                            @if (Route::has('community.index'))
                                <li><a href="{{ route('community.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Community') }}</a></li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            @if (Route::has('products.index'))
                                <li><a href="{{ route('products.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Products') }}</a></li>
                            @endif
                            @if (Route::has('services.index'))
                                <li><a href="{{ route('services.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Services') }}</a></li>
                            @endif
                            @if (Route::has('hire'))
                                <li><a href="{{ route('hire') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Hire an expert') }}</a></li>
                            @endif
                            @if (Route::has('login'))
                                <li><a href="{{ route('login') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('Sign in') }}</a></li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Placement') }}</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li><a href="{{ route('home') }}#programme" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('The programme') }}</a></li>
                            <li><a href="{{ route('home') }}#how-it-works" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ __('How it works') }}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-zinc-200 dark:border-zinc-800">
                    <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8 dark:text-zinc-400">
                        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Livepetal') }} Systems Limited. {{ __('Learn. Build. Track. Grow.') }}</p>
                        <p>{{ __('Built for honest trainee documentation.') }}</p>
                    </div>
                </div>
            </footer>

            @persist('toast')
                <flux:toast.group>
                    <flux:toast />
                </flux:toast.group>
            @endpersist

            @fluxScripts
        </div>
    </body>
</html>