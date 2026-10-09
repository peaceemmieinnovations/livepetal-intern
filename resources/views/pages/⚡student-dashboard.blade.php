<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        $placement = $user->placement;

        return $this->view([
            'user' => $user,
            'profile' => $profile,
            'placement' => $placement,
            'progress' => $placement?->progressPercentage() ?? 0,
            'reportsNeedingAction' => $user->reports()->needingAction()->count(),
            'recentReports' => $user->reports()->latest()->limit(5)->get(),
            'summariesAwaitingReview' => $profile?->weeklySummaries()->awaitingReview()->count() ?? 0,
            'skills' => $profile?->skills()->count() ?? 0,
            'certificates' => $user->courseCertificates()->count(),
            'onboarding' => $profile?->onboardingProgress() ?? 0,
            'canOpenReports' => Route::has('student.reports.index'),
        ]);
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="2xl">{{ __('Trainee dashboard') }}</flux:heading>
            <flux:text class="mt-1 block">{{ $user->name }} · {{ $profile?->student_id_number ?? __('Placement pending') }}</flux:text>
        </div>
        @if ($placement)
            <flux:badge color="emerald" variant="solid" class="w-fit">
                {{ ucwords(str_replace('_', ' ', $placement->status)) }}
            </flux:badge>
        @endif
    </header>

    @if (! $placement)
        <x-empty-state
            :title="__('Your placement has not been approved yet')"
            :body="__('Once an administrator approves your account and assigns a supervisor, your placement window, weekly summaries and report deadlines appear here. Everything you have filled in so far is saved.')"
        />
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Placement progress')" :value="$progress.'%'" :hint="$placement ? __(':days days remaining', ['days' => $placement->daysRemaining()]) : __('No active placement')" />
        <x-stat-card :label="__('Reports needing action')" :value="$reportsNeedingAction" :hint="__('Submitted or awaiting your revision')" />
        <x-stat-card :label="__('Summaries awaiting review')" :value="$summariesAwaitingReview" :hint="__('Weekly logs sent to your supervisor')" />
        <x-stat-card :label="__('Skills demonstrated')" :value="$skills" :hint="__(':count certificates earned', ['count' => $certificates])" />
    </div>

    @if ($placement)
        <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <div class="flex items-baseline justify-between gap-4">
                <flux:heading size="lg">{{ __('Placement window') }}</flux:heading>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    {{ $placement->starts_on->format('d M Y') }} — {{ $placement->ends_on->format('d M Y') }}
                </p>
            </div>
            <flux:progress
                color="emerald"
                class="mt-4"
                style="--flux-progress-percentage: {{ $progress }}%"
                aria-label="{{ __('Placement progress') }}"
            />
            <div class="mt-3 grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Supervisor') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $placement->supervisor?->name ?? __('Not assigned') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Training area') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $placement->training_area }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Days completed') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $placement->daysElapsed() }} / {{ $placement->totalDays() }}</p>
                </div>
            </div>
        </section>
    @endif

    <section class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Your reports') }}</flux:heading>

        @if ($recentReports->isEmpty())
            <x-empty-state
                :title="__('No reports yet')"
                :body="__('Your SIWES report is assembled from work you actually record: activities, skills, projects and supervisor feedback. Start filling in your activity log and the report builds itself from real entries.')"
                :actionLabel="$canOpenReports ? __('Open report centre') : null"
                :actionHref="$canOpenReports ? route('student.reports.index') : null"
            />
        @else
            <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                    @foreach ($recentReports as $report)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $report->title }}</p>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $report->created_at->format('d M Y') }}</p>
                            </div>
                            @php
                                $statusColor = [
                                    'success' => 'green',
                                    'warning' => 'yellow',
                                    'danger' => 'red',
                                    'info' => 'blue',
                                ][$report->status->badgeVariant()] ?? 'zinc';
                            @endphp
                            <flux:badge color="{{ $statusColor }}">{{ $report->status->label() }}</flux:badge>
                        </div>
                    @endforeach
            </div>
        @endif
    </section>
</div>
