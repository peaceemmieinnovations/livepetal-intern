<x-layouts::public title="{{ __('Services') }}">
    <section class="relative isolate overflow-hidden bg-zinc-950">
        <div class="hero-grid absolute inset-0 opacity-25" aria-hidden="true"></div>
        <div class="hero-orb absolute -left-24 top-0 size-80 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="hero-orb-delay absolute -right-20 top-16 size-80 rounded-full bg-cyan-400/20 blur-3xl" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 mx-auto h-64 max-w-4xl bg-[radial-gradient(closest-side,rgba(16,185,129,0.18),transparent)]" aria-hidden="true"></div>
        <div class="relative mx-auto grid w-full max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16 lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-300">{{ __('Livepetal Services') }}</p>
                <h1 class="mt-5 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ __('Services that make work honest.') }}</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-zinc-300">{{ __('From structured SIWES placements to free training and vetted talent, every service is built around real work, clear records and trust.') }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <flux:button tag="a" href="#services" variant="primary">{{ __('Explore services') }}</flux:button>
                    <flux:button tag="a" href="{{ route('hire') }}#enquire" variant="ghost" wire:navigate class="border border-white/20 text-white hover:bg-white/10">{{ __('Talk to our team') }}</flux:button>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute -inset-4 rounded-[2rem] bg-brand-500/20 blur-2xl" aria-hidden="true"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-white/15 bg-zinc-900 shadow-2xl shadow-black/40">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&amp;fit=crop&amp;w=1000&amp;q=85" alt="Team planning together" class="aspect-[5/4] w-full object-cover" />
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-zinc-950/90 to-transparent px-6 pb-6 pt-24">
                        <p class="text-sm font-medium text-white">{{ __('Every service points back to real, verifiable work.') }}</p>
                    </div>
                </div>
                <div class="absolute -bottom-5 -right-2 hidden rounded-2xl border border-white/15 bg-zinc-900/90 px-5 py-4 shadow-xl backdrop-blur-xl sm:block">
                    <p class="text-2xl font-semibold text-brand-400">{{ __('6') }}</p>
                    <p class="text-xs text-zinc-300">{{ __('Ways to work with us') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">{{ __('What we do') }}</p>
            <flux:heading size="2xl" class="mt-4">{{ __('One platform, six ways to grow.') }}</flux:heading>
            <flux:text class="mt-3 block">{{ __('Choose the service that matches your goals — or combine them.') }}</flux:text>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @php
                $services = [
                    [
                        'icon' => 'academic-cap',
                        'title' => __('SIWES Internship Platform'),
                        'body' => __('A structured home for placement, learning, reports and the portfolio — everything an intern needs, calmly organised.'),
                        'route' => 'home',
                        'anchor' => '#programme',
                    ],
                    [
                        'icon' => 'book-open',
                        'title' => __('Free Training School'),
                        'body' => __('Practical, self-paced courses that build real skills. Earn verifiable certificates before you ever apply.'),
                        'route' => 'courses.index',
                        'anchor' => null,
                    ],
                    [
                        'icon' => 'clipboard-document-list',
                        'title' => __('Placement Coordination'),
                        'body' => __('Trainees are matched to real roles with clear expectations, supervision and a simple sign-off at the end.'),
                        'route' => 'products.index',
                        'anchor' => null,
                    ],
                    [
                        'icon' => 'users',
                        'title' => __('Vetted Talent Hire'),
                        'body' => __('Meet interns and graduates whose work is captured as it happens — proof of effort, not a polished CV.'),
                        'route' => 'hire',
                        'anchor' => null,
                    ],
                    [
                        'icon' => 'wrench-screwdriver',
                        'title' => __('Engineering Consulting'),
                        'body' => __('Livepetal’s engineers build practical software with your team, from scoping to delivery.'),
                        'route' => 'hire',
                        'anchor' => '#enquire',
                    ],
                    [
                        'icon' => 'hand-raised',
                        'title' => __('Partnerships & Custom Programmes'),
                        'body' => __('Co-design placement or training programmes that fit your institution, company or community.'),
                        'route' => 'hire',
                        'anchor' => '#enquire',
                    ],
                ];
            @endphp

            @foreach ($services as $service)
                @if (Route::has($service['route']))
                    <article class="group flex flex-col rounded-3xl border border-zinc-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                            <flux:icon :name="$service['icon']" class="size-6" />
                        </span>
                        <h2 class="mt-6 text-lg font-semibold text-zinc-900 dark:text-white">{{ $service['title'] }}</h2>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $service['body'] }}</p>
                        <div class="mt-6 flex-1 items-end">
                            <flux:button
                                tag="a"
                                :href="route($service['route']).($service['anchor'] ?? '')"
                                variant="ghost"
                                size="sm"
                                icon-trailing="arrow-right"
                                :wire:navigate="$service['route'] !== 'hire' && $service['route'] !== 'products'"
                                class="px-0 text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                            >
                                {{ __('Learn more') }}
                            </flux:button>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </section>

    <section class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/40">
        <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-3 lg:gap-12 lg:px-8">
            <div class="lg:col-span-1">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">{{ __('How it works') }}</p>
                <flux:heading size="2xl" class="mt-4">{{ __('A calm path from first hello to real results.') }}</flux:heading>
            </div>
            <div class="grid gap-5 sm:grid-cols-3 lg:col-span-2">
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-3xl font-semibold text-brand-600 dark:text-brand-400">{{ __('01') }}</p>
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Tell us your goal') }}</p>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Reach out through a quick enquiry and our team listens first.') }}</p>
                </div>
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-3xl font-semibold text-brand-600 dark:text-brand-400">{{ __('02') }}</p>
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('We scope it together') }}</p>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('We agree on the shape of the work, the timeline and who does what.') }}</p>
                </div>
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-3xl font-semibold text-brand-600 dark:text-brand-400">{{ __('03') }}</p>
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Delivered with proof') }}</p>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Work is documented as it happens, so outcomes are easy to verify.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] bg-zinc-950 px-6 py-16 text-center sm:px-10">
            <div class="hero-grid absolute inset-0 opacity-20" aria-hidden="true"></div>
            <div class="hero-orb-delay absolute -right-16 -top-10 size-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="relative">
                <flux:heading size="2xl" class="text-white">{{ __('Let’s build something worth proving.') }}</flux:heading>
                <flux:text class="mx-auto mt-4 block max-w-xl text-zinc-300">{{ __('Whether you are a trainee, a company or a partner institution, there is a Livepetal service ready for you.') }}</flux:text>
                <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                    @if (Route::has('hire'))
                        <flux:button tag="a" href="{{ route('hire') }}#enquire" variant="primary" wire:navigate>{{ __('Start an enquiry') }}</flux:button>
                    @endif
                    @if (Route::has('products.index'))
                        <flux:button tag="a" :href="route('products.index')" variant="ghost" wire:navigate class="border border-white/20 text-white hover:bg-white/10">{{ __('See our products') }}</flux:button>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>