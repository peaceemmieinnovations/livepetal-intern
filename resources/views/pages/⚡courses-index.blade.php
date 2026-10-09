<?php

use App\Enums\CourseLevel;
use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $level = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLevel(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $courses = Course::query()
            ->published()
            ->with(['skill'])
            ->withCount(['modules as module_count' => fn ($query) => $query->where('is_published', true)])
            ->withCount(['enrollments' => fn ($query) => $query])
            ->when(filled($this->level), fn ($query) => $query->where('level', $this->level))
            ->when(filled($this->search), fn ($query) => $query->where(function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('summary', 'like', '%'.$this->search.'%');
            }))
            ->orderByDesc('published_at')
            ->paginate(9);

        $enrollments = $user
            ? $user->courseEnrollments()
                ->whereIn('course_id', collect($courses->items())->pluck('id'))
                ->get()
                ->keyBy('course_id')
            : collect();

        return $this->view([
            'courses' => $courses,
            'enrollments' => $enrollments,
            'user' => $user,
            'levels' => CourseLevel::cases(),
        ])->layout('layouts::public')->title(__('Free courses'));
    }
};
?>

<div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <header class="max-w-2xl">
        <flux:heading size="2xl">{{ __('Free courses') }}</flux:heading>
        <flux:text class="mt-2 block">
            {{ __('Every course is free, self-paced and designed around the skills Livepetal looks for in an intern. Anyone can start one — no prerequisite, no payment.') }}
        </flux:text>
    </header>

    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search courses…') }}"
            class="sm:max-w-sm"
        />

        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="$set('level', '')" :variant="$level === '' ? 'primary' : 'ghost'" size="sm">
                {{ __('All levels') }}
            </flux:button>

            @foreach ($levels as $courseLevel)
                <flux:button wire:click="$set('level', '{{ $courseLevel->value }}')" :variant="$level === $courseLevel->value ? 'primary' : 'ghost'" size="sm">
                    {{ $courseLevel->label() }}
                </flux:button>
            @endforeach
        </div>
    </div>

    @if ($courses->isEmpty())
        <x-empty-state
            class="mt-10"
            :title="__('No courses match your search')"
            :body="__('Try a different keyword or clear the level filter to see the full catalogue.')"
        />
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <article class="flex flex-col rounded-xl border border-zinc-200 bg-white p-6 transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
                        <flux:badge :color="$course->level->badgeVariant()" size="sm" inset="top bottom">
                            {{ $course->level->label() }}
                        </flux:badge>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            @if ($enrollments->has($course->id))
                                {{ __(':percent% complete', ['percent' => $enrollments->get($course->id)->progress_percent]) }}
                            @else
                                {{ __(':minutes min', ['minutes' => $course->estimated_minutes]) }}
                            @endif
                        </span>
                    </div>

                    <h2 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        <a href="{{ route('courses.show', $course) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-500">
                            {{ $course->title }}
                        </a>
                    </h2>

                    <p class="mt-2 flex-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ $course->summary }}
                    </p>

                    <div class="mt-5 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                        <span>{{ __(':count modules', ['count' => $course->module_count ?? 0]) }}</span>
                        <span>{{ $course->skill?->name ?? __('General') }}</span>
                    </div>

                    <flux:button
                        :href="route('courses.show', $course)"
                        wire:navigate
                        :variant="$enrollments->has($course->id) ? 'ghost' : 'primary'"
                        size="sm"
                        class="mt-4 justify-center"
                    >
                        {{ $enrollments->has($course->id) ? __('Continue') : __('Start course') }}
                    </flux:button>
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $courses->links() }}
        </div>
    @endif
</div>