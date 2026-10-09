<?php

use App\Models\Placement;
use App\Models\Report;
use App\Models\WeeklySummary;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $user = auth()->user();

        $placements = Placement::query()
            ->where('supervisor_id', $user->id)
            ->with(['student:id,name,avatar_path', 'student.studentProfile:id,user_id,level'])
            ->orderByDesc('starts_on')
            ->get();

        $traineeIds = $placements->pluck('student_id')->filter()->unique();

        $belongsToTrainee = fn ($query) => $query->whereIn('user_id', $traineeIds);

        return $this->view([
            'user' => $user,
            'placements' => $placements,
            'traineeCount' => $traineeIds->count(),
            'reportsToReview' => Report::query()->needingAction()
                ->whereHas('studentProfile', $belongsToTrainee)
                ->count(),
            'summariesToReview' => WeeklySummary::query()->awaitingReview()
                ->whereHas('studentProfile', $belongsToTrainee)
                ->count(),
            'activeTrainees' => $placements->filter(fn (Placement $placement): bool => $placement->isActive())->count(),
        ]);
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1">
        <flux:heading size="2xl">{{ __('Supervisor dashboard') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Review, give feedback and sign off the work your trainees record.') }}</flux:text>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Trainees assigned')" :value="$traineeCount" :hint="__('Across every active cohort')" />
        <x-stat-card :label="__('Active placements')" :value="$activeTrainees" :hint="__('Currently inside the training window')" />
        <x-stat-card :label="__('Reports to review')" :value="$reportsToReview" :hint="__('Submitted and waiting on you')" />
        <x-stat-card :label="__('Summaries to review')" :value="$summariesToReview" :hint="__('Weekly logs awaiting feedback')" />
    </div>

    <section class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Your trainees') }}</flux:heading>

        @if ($placements->isEmpty())
            <x-empty-state
                :title="__('No trainees assigned yet')"
                :body="__('When an administrator assigns you a placement, each trainee appears here with their placement window, completion percentage and anything waiting for your review.')"
            />
        @else
            <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                @foreach ($placements as $placement)
                    @php
                        $progress = $placement->progressPercentage();
                    @endphp
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $placement->student?->name ?? __('Unassigned trainee') }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $placement->training_area }} · {{ $placement->starts_on->format('d M Y') }} — {{ $placement->ends_on->format('d M Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 sm:w-64">
                            <flux:progress
                                color="emerald"
                                class="grow"
                                style="--flux-progress-percentage: {{ $progress }}%"
                                aria-label="{{ __('Placement progress') }}"
                            />
                            <span class="w-10 shrink-0 text-right text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $progress }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
