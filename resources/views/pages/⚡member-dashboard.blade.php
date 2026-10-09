<?php

use App\Models\Course;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $user = auth()->user();

        return $this->view([
            'user' => $user,
            'awaitingApproval' => $user->isAwaitingApproval(),
            'enrollments' => $user->courseEnrollments()
                ->with('course:id,title,slug,status')
                ->orderByDesc('last_accessed_at')
                ->limit(5)
                ->get(),
            'activeCourses' => $user->courseEnrollments()->whereNull('completed_at')->count(),
            'certificates' => $user->courseCertificates()->count(),
            'posts' => $user->communityPosts()->count(),
            'catalogueSize' => Course::query()->published()->count(),
            'canBrowseCourses' => Route::has('courses.index'),
            'canOpenCommunity' => Route::has('community.index'),
        ]);
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="2xl">{{ __('Welcome back, :name', ['name' => $user->name]) }}</flux:heading>
            <flux:text class="mt-1 block">{{ __('Here is where your Livepetal journey stands today.') }}</flux:text>
        </div>
    </header>

    @if ($awaitingApproval)
        <flux:callout variant="warning" icon="clock" :heading="__('Your intern access is awaiting approval')" class="items-start">
            <flux:callout.text>
                {{ __('You can browse public content and take free courses right away. An administrator will review your account before trainee features such as placements, reports and the portfolio are unlocked.') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Courses in progress')" :value="$activeCourses" :hint="__('Free, self-paced')" />
        <x-stat-card :label="__('Certificates earned')" :value="$certificates" :hint="__('Verifiable, signed by Livepetal')" />
        <x-stat-card :label="__('Catalogue courses')" :value="$catalogueSize" :hint="__('Published and open to everyone')" />
        <x-stat-card :label="__('Posts shared')" :value="$posts" :hint="__('Questions, problems and discussions')" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Continue learning') }}</flux:heading>

            @if ($enrollments->isEmpty())
                <x-empty-state
                    :title="__('You have not started a course yet')"
                    :body="__('Free courses cover the skills Livepetal looks for in an intern. Pick one and start today — there is nothing to pay and no prerequisite to clear.')"
                    :actionLabel="$canBrowseCourses ? __('Browse courses') : null"
                    :actionHref="$canBrowseCourses ? route('courses.index') : null"
                />
            @else
                <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                    @foreach ($enrollments as $enrollment)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $enrollment->course?->title ?? __('Untitled course') }}
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $enrollment->completed_at ? __('Completed :date', ['date' => $enrollment->completed_at->format('d M Y')]) : __(':percent% complete', ['percent' => $enrollment->progress_percent]) }}
                                </p>
                            </div>
                            <flux:progress
                                color="emerald"
                                class="w-24 shrink-0"
                                style="--flux-progress-percentage: {{ $enrollment->progress_percent }}%"
                                aria-label="{{ __('Progress') }}"
                            />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Join the community') }}</flux:heading>

            <x-empty-state
                :title="__('Ask a question, post a problem, share what you built')"
                :body="__('The community is open to everyone on the platform. When you solve a problem, mark it as solved so the next person finds the answer in seconds.')"
                :actionLabel="$canOpenCommunity ? __('Open the community') : null"
                :actionHref="$canOpenCommunity ? route('community.index') : null"
            />
        </section>
    </div>
</div>
