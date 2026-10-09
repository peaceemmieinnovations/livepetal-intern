<x-layouts::public :title="__('SIWES training, honestly documented')">
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-zinc-950">
        <div class="hero-grid absolute inset-0 opacity-30" aria-hidden="true"></div>
        <div class="hero-orb absolute -left-24 top-8 size-72 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="hero-orb-delay absolute -right-20 bottom-0 size-80 rounded-full bg-cyan-400/20 blur-3xl" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 mx-auto h-72 max-w-4xl bg-[radial-gradient(closest-side,rgba(16,185,129,0.16),transparent)]" aria-hidden="true"></div>
        <div class="relative mx-auto grid w-full max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16 lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <flux:badge size="sm" pill class="mb-6 border border-white/15 bg-white/10 text-zinc-100">
                    <flux:icon name="sparkles" variant="micro" class="me-1 text-brand-400" />
                    {{ __('Run by Livepetal Systems Limited') }}
                </flux:badge>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-300">{{ __('A better way to do SIWES') }}</p>
                <h1 class="mt-5 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ __('Learn it. Build it. Prove it.') }}</h1>
                <flux:text size="xl" class="mt-6 block max-w-xl text-zinc-300">
                    {{ __('Take free courses, join an honest community of trainees and keep a verified record of the work you actually did — not one filled in at the last minute.') }}
                </flux:text>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @auth
                        <flux:button :href="route(auth()->user()->dashboardRouteName())" variant="primary" wire:navigate class="shadow-lg shadow-brand-900/40">
                            {{ __('Go to your dashboard') }}
                        </flux:button>
                    @else
                        @if (Route::has('register'))
                            <flux:button :href="route('register')" variant="primary" wire:navigate class="shadow-lg shadow-brand-900/40">
                                {{ __('Create a free account') }}
                            </flux:button>
                        @endif
                    @endauth
                    @if (Route::has('courses.index'))
                        <flux:button :href="route('courses.index')" variant="ghost" wire:navigate class="border border-white/20 text-white hover:bg-white/10">
                            {{ __('Browse free courses') }}
                        </flux:button>
                    @endif
                </div>
                <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-sm text-zinc-300">
                    <span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-brand-400"></span>{{ __('Always free to start') }}</span>
                    <span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-brand-400"></span>{{ __('Built for real placements') }}</span>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-xl lg:max-w-none">
                <div class="absolute -inset-4 rounded-[2rem] bg-brand-500/20 blur-2xl" aria-hidden="true"></div>
                <div class="relative grid grid-cols-12 gap-3 sm:gap-4">
                    <div class="col-span-7 overflow-hidden rounded-3xl border border-white/15 bg-zinc-900 shadow-2xl shadow-black/40">
                        <img src="https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&amp;fit=crop&amp;w=1000&amp;q=85" alt="Students collaborating" class="hero-photo h-72 w-full object-cover sm:h-96" />
                    </div>
                    <div class="col-span-5 flex flex-col gap-3 sm:gap-4">
                        <div class="overflow-hidden rounded-3xl border border-white/15 bg-zinc-900 shadow-xl shadow-black/30">
                            <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&amp;fit=crop&amp;w=700&amp;q=85" alt="Team working together" class="hero-photo-delayed h-36 w-full object-cover sm:h-48" />
                        </div>
                        <div class="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur-xl sm:p-6">
                            <div class="flex size-10 items-center justify-center rounded-2xl bg-brand-400 text-brand-950"><flux:icon name="bolt" class="size-5" /></div>
                            <p class="mt-4 text-3xl font-semibold text-white">{{ __('100%') }}</p>
                            <p class="mt-1 text-sm leading-5 text-zinc-300">{{ __('Built around honest, practical work.') }}</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-5 left-5 rounded-2xl border border-white/15 bg-zinc-900/90 px-4 py-3 shadow-xl backdrop-blur-xl sm:left-8">
                        <p class="text-xs font-medium text-white">{{ __('From first lesson to verified record') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="border-y border-zinc-200 bg-gradient-to-r from-brand-50 via-white to-cyan-50 dark:border-zinc-800 dark:from-brand-950/40 dark:via-zinc-950 dark:to-zinc-950">
        <div class="mx-auto grid w-full max-w-7xl grid-cols-2 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ($stats as $stat)
                <div class="text-center transition-transform duration-300 hover:-translate-y-1">
                    <p class="text-3xl font-bold text-brand-700 tabular-nums dark:text-brand-400">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Visual programme introduction --}}
    <section class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8">
        <div class="relative order-2 lg:order-1">
            <div class="absolute -left-6 -top-6 size-32 rounded-full bg-brand-100 blur-2xl dark:bg-brand-900/40" aria-hidden="true"></div>
            <div class="relative overflow-hidden rounded-[2rem] bg-zinc-200 shadow-2xl shadow-zinc-950/10 dark:bg-zinc-800">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&amp;fit=crop&amp;w=1200&amp;q=85" alt="Student working on a laptop" class="aspect-[4/3] w-full object-cover" />
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-zinc-950/85 via-zinc-950/15 to-transparent px-6 pb-6 pt-20">
                    <p class="text-sm font-medium text-white">{{ __('Practical skills, documented as you build.') }}</p>
                </div>
            </div>
            <div class="absolute -bottom-5 -right-2 hidden rounded-2xl border border-zinc-200 bg-white px-5 py-4 shadow-xl sm:block dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-2xl font-semibold text-brand-600 dark:text-brand-400">{{ __('01') }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Start with a real plan') }}</p>
            </div>
        </div>
        <div class="order-1 lg:order-2">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">{{ __('Made for momentum') }}</p>
            <flux:heading size="2xl" class="mt-4">{{ __('An internship experience that looks forward.') }}</flux:heading>
            <flux:text class="mt-5 block max-w-xl text-base leading-7">
                {{ __('Move from learning to doing with everything in one calm, practical space. No scattered notes. No last-minute guesswork.') }}
            </flux:text>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <flux:icon name="academic-cap" class="size-6 text-brand-600 dark:text-brand-400" />
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Learn by doing') }}</p>
                    <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Useful, self-paced lessons that fit your placement.') }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <flux:icon name="shield-check" class="size-6 text-brand-600 dark:text-brand-400" />
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Keep it credible') }}</p>
                    <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('A clear record that makes your work easy to trust.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Moving visual reel --}}
    <section class="overflow-hidden border-y border-zinc-200 bg-zinc-950 py-6 dark:border-zinc-800">
        <div class="image-marquee flex w-max gap-4 px-4 sm:gap-6">
            @foreach ([
                ['https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=700&q=85', __('Collaborate')],
                ['https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=700&q=85', __('Build')],
                ['https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=700&q=85', __('Present')],
                ['https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=700&q=85', __('Learn')],
                ['https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=700&q=85', __('Grow')],
                ['https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=700&q=85', __('Collaborate')],
                ['https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=700&q=85', __('Build')],
            ] as [$image, $label])
                <figure class="relative h-36 w-56 shrink-0 overflow-hidden rounded-2xl border border-white/10 sm:h-44 sm:w-72">
                    <img src="{{ $image }}" alt="{{ $label }}" class="h-full w-full object-cover opacity-80 transition duration-500 hover:scale-110 hover:opacity-100" />
                    <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-zinc-950/90 to-transparent px-4 pb-3 pt-10 text-sm font-medium text-white">{{ $label }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- What you will find here --}}
    <section class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <flux:heading size="2xl">{{ __('Everything you need for a real placement') }}</flux:heading>
            <flux:text class="mt-3 block">
                {{ __('A single home for preparing, working and proving you did it.') }}
            </flux:text>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @if (Route::has('courses.index'))
                <a href="{{ route('courses.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition group-hover:scale-110 dark:bg-brand-950/50 dark:text-brand-400"><flux:icon name="academic-cap" class="size-6" /></span>
                    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-600 dark:group-hover:text-brand-500">{{ __('Free courses') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Start anything, no payment needed. Certificates for finishing.') }}</p>
                </a>
            @endif

            @if (Route::has('community.index'))
                <a href="{{ route('community.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition group-hover:scale-110 dark:bg-brand-950/50 dark:text-brand-400"><flux:icon name="chat-bubble-left-ellipsis" class="size-6" /></span>
                    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-600 dark:group-hover:text-brand-500">{{ __('Community') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Ask questions, share builds and help fellow trainees.') }}</p>
                </a>
            @endif

            @if (Route::has('blog.index'))
                <a href="{{ route('blog.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition group-hover:scale-110 dark:bg-brand-950/50 dark:text-brand-400"><flux:icon name="book-open" class="size-6" /></span>
                    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-600 dark:group-hover:text-brand-500">{{ __('Insights') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Guides and stories from the engineering team.') }}</p>
                </a>
            @endif

            @if (Route::has('hire'))
                <a href="{{ route('hire') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition group-hover:scale-110 dark:bg-brand-950/50 dark:text-brand-400"><flux:icon name="briefcase" class="size-6" /></span>
                    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-brand-600 dark:group-hover:text-brand-500">{{ __('Hire an expert') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Great engineering artisans trained here, ready for real work.') }}</p>
                </a>
            @endif
        </div>
    </section>

    {{-- Featured courses --}}
    <section class="relative overflow-hidden border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/40">
        <div class="absolute right-0 top-0 hidden h-full w-1/3 bg-[url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80')] bg-cover bg-center opacity-[0.035] lg:block dark:opacity-[0.06]" aria-hidden="true"></div>
        <div class="relative mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <flux:heading size="2xl">{{ __('Start with a course') }}</flux:heading>
                    <flux:text class="mt-3 block">
                        {{ __('Self-paced, free and built around what an intern actually ships.') }}
                    </flux:text>
                </div>
                @if (Route::has('courses.index'))
                    <flux:button :href="route('courses.index')" variant="ghost" wire:navigate class="hidden sm:inline-flex">
                        {{ __('View all courses') }}
                    </flux:button>
                @endif
            </div>

            @if ($featuredCourses->isEmpty())
                <flux:callout icon="academic-cap" class="mt-10">
                    {{ __('Courses are being added. Check back soon.') }}
                </flux:callout>
            @else
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredCourses as $course)
                        <article class="group relative flex flex-col overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-brand-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                            <div class="absolute right-0 top-0 size-24 rounded-bl-[3rem] bg-brand-50 transition duration-300 group-hover:scale-125 dark:bg-brand-950/40" aria-hidden="true"></div>
                            <div class="flex items-center justify-between gap-3">
                                <flux:badge :color="$course->level->badgeVariant()" size="sm" inset="top bottom">{{ $course->level->label() }}</flux:badge>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __(':minutes min', ['minutes' => $course->estimated_minutes]) }}</span>
                            </div>
                            <h3 class="relative mt-7 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route('courses.show', $course) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-500">{{ $course->title }}</a>
                            </h3>
                            <p class="mt-2 flex-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $course->summary }}</p>
                            <div class="mt-5 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ __(':count modules', ['count' => $course->module_count ?? 0]) }}</span>
                                <span>{{ $course->skill?->name ?? __('General') }}</span>
                            </div>
                            <flux:button :href="route('courses.show', $course)" wire:navigate variant="primary" size="sm" class="mt-4 justify-center">
                                {{ __('Start course') }}
                            </flux:button>
                        </article>
                    @endforeach
                </div>
            @endif

            @if (Route::has('courses.index'))
                <div class="mt-8 text-center sm:hidden">
                    <flux:button :href="route('courses.index')" variant="ghost" wire:navigate>{{ __('View all courses') }}</flux:button>
                </div>
            @endif
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="mx-auto w-full max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.65fr] lg:items-end">
            <div class="max-w-2xl">
            <flux:heading size="2xl">{{ __('How the programme works') }}</flux:heading>
            <flux:text class="mt-3 block">
                {{ __('From joining to a verifiable placement record.') }}
            </flux:text>
            </div>
            <div class="hidden overflow-hidden rounded-3xl lg:block">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&amp;fit=crop&amp;w=900&amp;q=85" alt="Students learning together" class="h-48 w-full object-cover transition duration-700 hover:scale-105" />
            </div>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                <p class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">1</p>
                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Create an account') }}</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Register free, then apply for intern access when you are ready.') }}</p>
            </div>
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                <p class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">2</p>
                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Learn and build') }}</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Work through free courses, ask the community and ship real projects.') }}</p>
            </div>
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                <p class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">3</p>
                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Document the work') }}</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Log activity, projects and reports as they happen — nothing fabricated.') }}</p>
            </div>
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                <p class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">4</p>
                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Get verified') }}</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Supervisors review your record so sign-off is honest and verifiable.') }}</p>
            </div>
        </div>
    </section>

    {{-- Community preview --}}
    <section class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/40">
        <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <flux:heading size="2xl">{{ __('Happening in the community') }}</flux:heading>
                    <flux:text class="mt-3 block">
                        {{ __('Real helpers, real builders. Shared by interns just like you.') }}
                    </flux:text>
                </div>
                @if (Route::has('community.index'))
                    <flux:button :href="route('community.index')" variant="ghost" wire:navigate class="hidden sm:inline-flex">
                        {{ __('Visit the community') }}
                    </flux:button>
                @endif
            </div>

            @if ($communityPosts->isEmpty())
                <flux:callout icon="chat-bubble-left-ellipsis" class="mt-10">
                    {{ __('Be the first to post. Your question might help the whole cohort.') }}
                </flux:callout>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($communityPosts as $post)
                        <article class="flex flex-col rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                            <div class="flex items-center justify-between gap-3">
                                <flux:badge :color="$post->is_solved ? 'emerald' : null" size="sm" inset="top bottom">
                                    {{ $post->post_type->label() }}{{ $post->is_solved ? ' · solved' : '' }}
                                </flux:badge>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-4 line-clamp-3 flex-1 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                                <a href="{{ route('community.show', $post) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-500">{{ $post->body }}</a>
                            </p>
                            <div class="mt-5 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $post->authorName() }}</span>
                                <span class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1"><flux:icon name="hand-thumb-up" variant="micro" /> {{ $post->likes_count }}</span>
                                    <span class="inline-flex items-center gap-1"><flux:icon name="chat-bubble-left-ellipsis" variant="micro" /> {{ $post->comments_count }}</span>
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if (Route::has('community.index'))
                <div class="mt-8 text-center sm:hidden">
                    <flux:button :href="route('community.index')" variant="ghost" wire:navigate>{{ __('Visit the community') }}</flux:button>
                </div>
            @endif
        </div>
    </section>

    {{-- Latest insights --}}
    <section class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div class="max-w-2xl">
                <flux:heading size="2xl">{{ __('Recent insights') }}</flux:heading>
                <flux:text class="mt-3 block">
                    {{ __('Notes and stories from the Livepetal engineering team.') }}
                </flux:text>
            </div>
            @if (Route::has('blog.index'))
                <flux:button :href="route('blog.index')" variant="ghost" wire:navigate class="hidden sm:inline-flex">
                    {{ __('All insights') }}
                </flux:button>
            @endif
        </div>

        @if ($latestPosts->isEmpty())
            <flux:callout icon="book-open" class="mt-10">
                {{ __('New insights are being written.') }}
            </flux:callout>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestPosts as $post)
                        <article class="flex flex-col rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-brand-700">
                        <div class="flex items-center justify-between gap-3">
                            @if ($post->primaryCategory)
                                <flux:badge size="sm" inset="top bottom">{{ $post->primaryCategory->name }}</flux:badge>
                            @endif
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $post->published_at->format('M j, Y') }}</span>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="{{ route('blog.show', $post) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-500">{{ $post->title }}</a>
                        </h3>
                        <p class="mt-2 flex-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            {{ $post->excerpt ?? Str::limit(strip_tags($post->body), 120) }}
                        </p>
                        <p class="mt-5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $post->author?->name }} · {{ __(':minutes min read', ['minutes' => $post->reading_minutes]) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif

        @if (Route::has('blog.index'))
            <div class="mt-8 text-center sm:hidden">
                <flux:button :href="route('blog.index')" variant="ghost" wire:navigate>{{ __('All insights') }}</flux:button>
            </div>
        @endif
    </section>

    {{-- Products & hire --}}
    <section id="programme" class="mx-auto w-full max-w-7xl scroll-mt-24 px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            @if (Route::has('products.index'))
                <div class="rounded-2xl border border-brand-200 bg-brand-50 p-8 dark:border-brand-800 dark:bg-brand-950/40">
                    <flux:icon name="building-office-2" class="size-8 text-brand-600 dark:text-brand-500" />
                    <flux:heading class="mt-4">{{ __('Built by Livepetal Systems') }}</flux:heading>
                    <flux:text class="mt-2 block">
                        {{ __('The same team ships software for real customers — and this platform is one of those products.') }}
                    </flux:text>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <flux:button :href="route('products.index')" variant="primary" wire:navigate>{{ __('Explore our products') }}</flux:button>
                    </div>
                </div>
            @endif

            @if (Route::has('hire'))
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <flux:icon name="shield-check" class="size-8 text-brand-600 dark:text-brand-500" />
                    <flux:heading class="mt-4">{{ __('Hire an expert') }}</flux:heading>
                    <flux:text class="mt-2 block">
                        {{ __('Trainees who document honest, verified work leave with a portfolio you can trust.') }}
                    </flux:text>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <flux:button :href="route('hire')" variant="primary" wire:navigate>{{ __('Tell us what you need') }}</flux:button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Final CTA --}}
    @guest
        @if (Route::has('register'))
            <section class="mx-auto w-full max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-[2rem] border border-brand-200 bg-gradient-to-br from-brand-50 via-white to-cyan-50 px-8 py-14 text-center shadow-xl shadow-brand-950/5 dark:border-brand-900 dark:from-brand-950/50 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="absolute -right-16 -top-16 size-52 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                    <flux:heading size="2xl">{{ __('Start your internship the right way') }}</flux:heading>
                    <flux:text class="mx-auto mt-3 block max-w-xl">
                        {{ __('Create a free account and begin your first course today. No card, no gate — just honest, structured training.') }}
                    </flux:text>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <flux:button :href="route('register')" wire:navigate variant="primary"  data-test="register-cta">
                            {{ __('Create a free account') }}
                        </flux:button>
                        @if (Route::has('login'))
                            <flux:button :href="route('login')" wire:navigate variant="ghost" >
                                {{ __('I already have an account') }}
                            </flux:button>
                        @endif
                    </div>
                    </div>
                </div>
            </section>
        @endif
    @endguest
</x-layouts::public>
