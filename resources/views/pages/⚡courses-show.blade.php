<?php

use App\Actions\CompleteCourseModule;
use App\Enums\ContentStatus;
use App\Models\Course;
use Livewire\Component;

new class extends Component
{
    public Course $course;

    public function mount(Course $course): void
    {
        abort_unless($course->status === ContentStatus::Published, 404);

        $this->course = $course->load(['skill', 'author']);
    }

    public function enroll(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->authorize('enroll', $this->course);

        if ($this->course->enrollmentFor(auth()->user()) === null) {
            auth()->user()->courseEnrollments()->create([
                'course_id' => $this->course->id,
                'enrolled_at' => now(),
                'started_at' => now(),
                'progress_percent' => 0,
            ]);
        }
    }

    public function completeModule(int $moduleId): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $enrollment = $this->course->enrollmentFor(auth()->user());
        abort_unless($enrollment !== null, 403);

        $this->authorize('complete', $enrollment);

        $module = $this->course->publishedModules()->findOrFail($moduleId);

        app(CompleteCourseModule::class)->execute($module, $enrollment);
    }

    public function render()
    {
        $user = auth()->user();
        $enrollment = $user ? $this->course->enrollmentFor($user) : null;

        return $this->view([
            'course' => $this->course,
            'user' => $user,
            'enrollment' => $enrollment,
            'modules' => $this->course->publishedModules()->get(),
            'certificate' => $enrollment?->certificate(),
        ])->layout('layouts::public')->title($this->course->title);
    }
};
?>

<div class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('courses.index')" wire:navigate>{{ __('Courses') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('courses.show', $course)" wire:navigate>{{ $course->title }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <header class="max-w-3xl">
        <div class="flex flex-wrap items-center gap-2">
            <flux:badge :color="$course->level->badgeVariant()" size="sm" inset="top bottom">
                {{ $course->level->label() }}
            </flux:badge>
            <flux:badge variant="subtle" size="sm" inset="top bottom">
                {{ __(':minutes min', ['minutes' => $course->estimated_minutes]) }}
            </flux:badge>
            @if ($course->skill)
                <flux:badge variant="subtle" size="sm" inset="top bottom">
                    {{ $course->skill->name }}
                </flux:badge>
            @endif
        </div>

        <flux:heading size="2xl" class="mt-4">{{ $course->title }}</flux:heading>
        <flux:text class="mt-3 block">{{ $course->summary }}</flux:text>

        @if ($course->description && $course->description !== $course->summary)
            <p class="mt-4 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $course->description }}</p>
        @endif
    </header>

    @if ($enrollment && $enrollment->isComplete())
        <flux:callout variant="success" icon="check-circle" class="mt-6 items-start">
            <flux:heading>{{ __('Course completed') }}</flux:heading>
            <flux:callout.text>
                {{ __('You finished every module. Your certificate is ready — it is signed and verifiable against the serial number.') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    <section class="mt-8">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg">{{ __('Modules') }}</flux:heading>
            @if ($enrollment)
                <div class="flex w-36 items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ min($enrollment->progress_percent, 100) }}%"></div>
                    </div>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $enrollment->progress_percent }}%</span>
                </div>
            @endif
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            @foreach ($modules as $index => $module)
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 px-5 py-4 last:border-0 dark:border-zinc-800">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Module :n', ['n' => $index + 1]) }}</span>
                            <span>·</span>
                            <span>{{ $module->content_type->label() }}</span>
                            <span>·</span>
                            <span>{{ __(':minutes min', ['minutes' => $module->duration_minutes]) }}</span>
                        </div>
                        <h3 class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $module->title }}</h3>
                        @if ($module->content)
                            <div class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400 [&:first-child]:mt-0 [&_ul]:list-disc [&_ul]:pl-4">
                                {!! nl2br(e($module->content)) !!}
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0">
                        @if ($module->isCompletedBy($enrollment))
                            <flux:badge variant="success" inset="top bottom">
                                {{ __('Done') }}
                            </flux:badge>
                        @elseif ($enrollment)
                            <flux:button
                                wire:click="completeModule({{ $module->id }})"
                                size="sm"
                            >
                                {{ __('Mark complete') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if ($enrollment)
                <flux:button variant="primary" :href="route('courses.index')" wire:navigate>
                    {{ __('Browse more courses') }}
                </flux:button>
            @else
                <flux:button variant="primary" wire:click="enroll">
                    {{ $user ? __('Start this course') : __('Sign in to start this course') }}
                </flux:button>
                @if (! $user)
                    <flux:text>{{ __('Or continue browsing the catalogue as a guest — enrolling is free.') }}</flux:text>
                @endif
            @endif

            @if ($certificate)
                <flux:badge variant="success" inset="top bottom">
                    {{ __('Certificate :serial', ['serial' => $certificate->serial_number]) }}
                </flux:badge>
            @endif
        </div>
    </section>
</div>