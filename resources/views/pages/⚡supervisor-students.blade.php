<?php

use App\Enums\ActivityStatus;
use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Models\ActivityLog;
use App\Models\Placement;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportReview;
use App\Models\WeeklySummary;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedPlacementId = null;

    public ?int $feedbackFor = null;

    public string $feedback = '';

    public function selectPlacement(int $placementId): void
    {
        $this->ownPlacement($placementId);

        $this->closeFeedback();
        $this->selectedPlacementId = $placementId;
    }

    public function backToList(): void
    {
        $this->selectedPlacementId = null;
        $this->closeFeedback();
    }

    public function openFeedback(int $recordId): void
    {
        if ($this->feedbackFor === $recordId) {
            $this->closeFeedback();

            return;
        }

        $this->feedbackFor = $recordId;
        $this->feedback = '';
    }

    public function closeFeedback(): void
    {
        $this->feedbackFor = null;
        $this->feedback = '';
    }

    public function publishActivity(int $id): void
    {
        $log = $this->activityOfSelected($id);

        $log->update([
            'status' => ActivityStatus::Published,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => $this->captureFeedback($id),
            'published_at' => now(),
        ]);

        $this->notifyRecord('Activity approved');
    }

    public function returnActivity(int $id): void
    {
        $log = $this->activityOfSelected($id);

        $log->update([
            'status' => ActivityStatus::Returned,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => $this->captureFeedback($id) ?? __('Please revise this entry and resubmit it.'),
        ]);

        $this->notifyRecord('Activity returned');
    }

    public function publishSummary(int $id): void
    {
        $summary = $this->summaryOfSelected($id);

        $summary->update([
            'status' => ActivityStatus::Published,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => $this->captureFeedback($id),
        ]);

        $this->notifyRecord('Weekly summary approved');
    }

    public function returnSummary(int $id): void
    {
        $summary = $this->summaryOfSelected($id);

        $summary->update([
            'status' => ActivityStatus::Returned,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => $this->captureFeedback($id) ?? __('Please revise this summary and resubmit it.'),
        ]);

        $this->notifyRecord('Weekly summary returned');
    }

    public function verifyProject(int $id): void
    {
        $project = $this->projectOfSelected($id);

        abort_unless(in_array($project->review_status, [ReviewStatus::Unreviewed, ReviewStatus::Pending], true), 422);

        $project->update([
            'review_status' => ReviewStatus::Verified,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verifier_note' => $this->captureFeedback($id),
        ]);

        $this->notifyRecord('Project verified');
    }

    public function rejectProject(int $id): void
    {
        $project = $this->projectOfSelected($id);

        abort_unless(in_array($project->review_status, [ReviewStatus::Unreviewed, ReviewStatus::Pending], true), 422);

        $project->update([
            'review_status' => ReviewStatus::Rejected,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verifier_note' => $this->captureFeedback($id) ?? __('Please revise or document this project better.'),
        ]);

        $this->notifyRecord('Project returned');
    }

    public function approveReport(int $id): void
    {
        $report = $this->reportOfSelected($id);

        abort_unless($report->status !== ReportStatus::Approved && $report->status !== ReportStatus::Final, 422);

        $comments = $this->captureFeedback($id);

        $report->update([
            'status' => ReportStatus::Approved,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $this->recordReview($report, ReportReview::DECISION_APPROVED, $comments);

        $this->notifyRecord('Report approved');
    }

    public function requestRevision(int $id): void
    {
        $report = $this->reportOfSelected($id);

        abort_unless($report->status !== ReportStatus::Approved && $report->status !== ReportStatus::Final, 422);

        $comments = $this->captureFeedback($id) ?? __('Please revise and resubmit this report.');

        $report->update([
            'status' => ReportStatus::RevisionRequested,
        ]);

        $this->recordReview($report, ReportReview::DECISION_REVISIONS, $comments);

        $this->notifyRecord('Revisions requested');
    }

    private function recordReview(Report $report, string $decision, ?string $comments): void
    {
        $version = $report->latestVersion();

        if (! $version) {
            return;
        }

        ReportReview::create([
            'report_version_id' => $version->id,
            'reviewer_id' => auth()->id(),
            'decision' => $decision,
            'comments' => $comments,
        ]);
    }

    private function notifyRecord(string $heading): void
    {
        $this->dispatch('toast-show', heading: __($heading), text: __('The trainee can see the outcome on their dashboard.'), variant: 'success');
    }

    private function captureFeedback(int $id): ?string
    {
        $note = $this->feedbackFor === $id ? trim($this->feedback) : '';

        $this->closeFeedback();

        return $note === '' ? null : $note;
    }

    private function selectedProfileId(): int
    {
        $placement = $this->ownPlacement($this->selectedPlacementId);
        $profileId = $placement->student?->studentProfile?->id;

        abort_unless($profileId, 404);

        return $profileId;
    }

    private function activityOfSelected(int $id): ActivityLog
    {
        return ActivityLog::query()
            ->where('student_profile_id', $this->selectedProfileId())
            ->findOrFail($id);
    }

    private function summaryOfSelected(int $id): WeeklySummary
    {
        return WeeklySummary::query()
            ->where('student_profile_id', $this->selectedProfileId())
            ->findOrFail($id);
    }

    private function projectOfSelected(int $id): Project
    {
        return Project::query()
            ->where('student_profile_id', $this->selectedProfileId())
            ->findOrFail($id);
    }

    private function reportOfSelected(int $id): Report
    {
        return Report::query()
            ->where('student_profile_id', $this->selectedProfileId())
            ->findOrFail($id);
    }

    private function ownPlacement(int $placementId): Placement
    {
        return Placement::query()
            ->where('supervisor_id', auth()->id())
            ->findOrFail($placementId);
    }

    public function render()
    {
        $user = auth()->user();

        $placements = Placement::query()
            ->where('supervisor_id', $user->id)
            ->with(['student:id,name,email,username,avatar_path', 'student.studentProfile:id,user_id,level'])
            ->orderByDesc('starts_on')
            ->get();

        $traineeIds = $placements->pluck('student_id')->filter()->unique();
        $profileIds = $placements->map(fn (Placement $placement): ?int => $placement->student?->studentProfile?->id)->filter()->unique();

        $keyed = fn ($collection) => $collection
            ->groupBy('student_profile_id')
            ->map->count();

        $activityCounts = $keyed(
            ActivityLog::query()
                ->select('id', 'student_profile_id')
                ->whereIn('student_profile_id', $profileIds)
                ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Returned->value])
                ->get(),
        );

        $summaryCounts = $keyed(
            WeeklySummary::query()
                ->select('id', 'student_profile_id')
                ->whereIn('student_profile_id', $profileIds)
                ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Returned->value])
                ->get(),
        );

        $reportCounts = $keyed(
            Report::query()
                ->select('id', 'student_profile_id')
                ->whereIn('student_profile_id', $profileIds)
                ->needingAction()
                ->get(),
        );

        $placements->each(function (Placement $placement) use ($activityCounts, $summaryCounts, $reportCounts): void {
            $profileId = $placement->student?->studentProfile?->id;

            $placement->pendingActivities = (int) ($activityCounts[$profileId] ?? 0);
            $placement->pendingSummaries = (int) ($summaryCounts[$profileId] ?? 0);
            $placement->reportsNeedingAction = (int) ($reportCounts[$profileId] ?? 0);
        });

        $view = [
            'placements' => $placements,
        ];

        if ($this->selectedPlacementId) {
            $profileId = $this->selectedProfileId();

            $view += [
                'placement' => $this->ownPlacement($this->selectedPlacementId),
                'pendingActivities' => ActivityLog::query()
                    ->with(['project'])
                    ->where('student_profile_id', $profileId)
                    ->where('status', ActivityStatus::Submitted->value)
                    ->orderByDesc('activity_date')
                    ->get(),
                'returnedActivities' => ActivityLog::query()
                    ->with(['project'])
                    ->where('student_profile_id', $profileId)
                    ->where('status', ActivityStatus::Returned->value)
                    ->orderByDesc('activity_date')
                    ->get(),
                'pendingSummaries' => WeeklySummary::query()
                    ->where('student_profile_id', $profileId)
                    ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Returned->value])
                    ->orderByDesc('week_starts_on')
                    ->get(),
                'pendingProjects' => Project::query()
                    ->where('student_profile_id', $profileId)
                    ->where('review_status', '!=', ReviewStatus::Verified->value)
                    ->orderByDesc('updated_at')
                    ->get(),
                'reportsToReview' => Report::query()
                    ->with(['currentVersion.reviews'])
                    ->where('student_profile_id', $profileId)
                    ->needingAction()
                    ->get(),
            ];
        }

        return $this->view($view)->layout('layouts::app')->title(__('My supervisees'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1">
        <flux:heading size="2xl">{{ __('My supervisees') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Review the work your trainees submit. Counts show what needs your attention.') }}</flux:text>
    </header>

    @if (! $selectedPlacementId)
        <div class="flex flex-col gap-4">
            @forelse ($placements as $placement)
                @php $progress = $placement->progressPercentage(); @endphp
                <article wire:key="placement-{{ $placement->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $placement->student?->name ?? __('Unassigned trainee') }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $placement->training_area }} · {{ $placement->starts_on->format('d M Y') }} — {{ $placement->ends_on->format('d M Y') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($placement->pendingActivities)
                                <flux:badge color="amber" size="sm">{{ __(':count activities', ['count' => $placement->pendingActivities]) }}</flux:badge>
                            @endif
                            @if ($placement->pendingSummaries)
                                <flux:badge color="amber" size="sm">{{ __(':count summaries', ['count' => $placement->pendingSummaries]) }}</flux:badge>
                            @endif
                            @if ($placement->reportsNeedingAction)
                                <flux:badge color="blue" size="sm">{{ __(':count reports', ['count' => $placement->reportsNeedingAction]) }}</flux:badge>
                            @endif
                            <flux:progress color="emerald" class="w-24" style="--flux-progress-percentage: {{ $progress }}%" aria-label="{{ __('Placement progress') }}" />
                            <flux:button wire:click="selectPlacement({{ $placement->id }})" size="sm" variant="primary">{{ __('Review') }}</flux:button>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state
                    :title="__('No trainees assigned yet')"
                    :body="__('When an administrator assigns you a placement, each trainee appears here with anything waiting for your review.')"
                />
            @endforelse
        </div>
    @else
        <flux:button wire:click="backToList" size="sm" variant="ghost" class="self-start">
            ← {{ __('Back to all trainees') }}
        </flux:button>

        <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <flux:heading size="lg">{{ $placement->student?->name }}</flux:heading>
            <flux:text class="mt-1 block">{{ $placement->training_area }} · {{ $placement->starts_on->format('d M Y') }} — {{ $placement->ends_on->format('d M Y') }}</flux:text>
        </section>

        @if ($reportsToReview->isNotEmpty())
            <section class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Submitted reports') }}</flux:heading>

                @foreach ($reportsToReview as $report)
                    <article wire:key="report-{{ $report->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $report->title }}
                                    <flux:badge :color="[
                                        'info' => 'blue',
                                        'warning' => 'amber',
                                        'danger' => 'red',
                                    ][$report->status->badgeVariant()] ?? 'zinc'" size="sm">{{ $report->status->label() }}</flux:badge>
                                </p>
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">v{{ $report->current_version }} · {{ $report->submitted_at?->format('d M Y H:i') }}</p>

                                @if ($report->latestVersion()?->reviews()->latest('id')->first()?->comments)
                                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:bg-amber-500/10 dark:text-amber-300">
                                        {{ __('Last feedback:') }} {{ $report->latestVersion()->reviews()->latest('id')->first()->comments }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-1">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button :href="route('reports.print', $report)" target="_blank" size="sm" variant="ghost">{{ __('Read draft') }}</flux:button>
                                    <flux:button wire:click="approveReport({{ $report->id }})" wire:confirm="{{ __('Approve this report as final?') }}" size="sm" variant="filled" color="emerald">{{ __('Approve') }}</flux:button>
                                    <flux:button wire:click="requestRevision({{ $report->id }})" size="sm" variant="ghost">{{ __('Request revisions') }}</flux:button>
                                </div>

                                @if ($feedbackFor === $report->id)
                                    <flux:textarea wire:model="feedback" rows="2" class="mt-2 w-80" placeholder="{{ __('Feedback the student will see') }}" />
                                @else
                                    <flux:button wire:click="openFeedback({{ $report->id }})" size="xs" variant="ghost">{{ __('Add feedback note') }}</flux:button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Activities awaiting review') }}</flux:heading>
                <flux:badge color="amber" size="sm">{{ $pendingActivities->count() }}</flux:badge>
            </div>

            @forelse ($pendingActivities->concat($returnedActivities) as $log)
                @php $isReturned = $log->status === \App\Enums\ActivityStatus::Returned; @endphp
                <article wire:key="activity-{{ $log->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $log->title }}
                                @if ($isReturned)
                                    <flux:badge color="red" size="sm">{{ __('Returned') }}</flux:badge>
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">{{ $log->activity_date->format('d M Y') }}@if ($log->project) · {{ $log->project->name }}@endif</p>
                            @if ($log->description)
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex flex-wrap gap-2">
                                <flux:button wire:click="publishActivity({{ $log->id }})" size="sm" variant="filled" color="emerald">{{ __('Approve') }}</flux:button>
                                <flux:button wire:click="returnActivity({{ $log->id }})" size="sm" variant="ghost">{{ __('Return to student') }}</flux:button>
                            </div>
                            @if ($feedbackFor === $log->id)
                                <flux:textarea wire:model="feedback" rows="2" class="mt-2 w-80" placeholder="{{ __('Optional note the student will see') }}" />
                            @else
                                <flux:button wire:click="openFeedback({{ $log->id }})" size="xs" variant="ghost">{{ __('Add note') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <flux:text>{{ __('Nothing waiting — great work keeping up.') }}</flux:text>
            @endforelse
        </section>

        <section class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Weekly summaries') }}</flux:heading>
                <flux:badge color="amber" size="sm">{{ $pendingSummaries->count() }}</flux:badge>
            </div>

            @forelse ($pendingSummaries as $summary)
                <article wire:key="summary-{{ $summary->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $summary->week_starts_on->format('d M') }} — {{ $summary->week_ends_on->format('d M Y') }}
                            </p>
                            @if ($summary->summary)
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ Str::limit($summary->summary, 220) }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex flex-wrap gap-2">
                                <flux:button wire:click="publishSummary({{ $summary->id }})" size="sm" variant="filled" color="emerald">{{ __('Approve') }}</flux:button>
                                <flux:button wire:click="returnSummary({{ $summary->id }})" size="sm" variant="ghost">{{ __('Return') }}</flux:button>
                            </div>
                            @if ($feedbackFor === $summary->id)
                                <flux:textarea wire:model="feedback" rows="2" class="mt-2 w-80" placeholder="{{ __('Optional note') }}" />
                            @else
                                <flux:button wire:click="openFeedback({{ $summary->id }})" size="xs" variant="ghost">{{ __('Add note') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <flux:text>{{ __('No summaries to review.') }}</flux:text>
            @endforelse
        </section>

        <section class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Project verification') }}</flux:heading>
                <flux:badge color="amber" size="sm">{{ $pendingProjects->count() }}</flux:badge>
            </div>

            @forelse ($pendingProjects as $project)
                <article wire:key="project-{{ $project->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $project->name }}</p>
                            @if ($project->description)
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ Str::limit($project->description, 220) }}</p>
                            @endif
                            <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                                @if ($project->repository_url)
                                    <a href="{{ $project->repository_url }}" target="_blank" rel="noopener" class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Code') }}</a>
                                @endif
                                @if ($project->live_url)
                                    <a href="{{ $project->live_url }}" target="_blank" rel="noopener" class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Live') }}</a>
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex flex-wrap gap-2">
                                <flux:button wire:click="verifyProject({{ $project->id }})" wire:confirm="{{ __('Verify this project?') }}" size="sm" variant="filled" color="emerald">{{ __('Verify') }}</flux:button>
                                <flux:button wire:click="rejectProject({{ $project->id }})" size="sm" variant="ghost">{{ __('Reject') }}</flux:button>
                            </div>
                            @if ($feedbackFor === $project->id)
                                <flux:textarea wire:model="feedback" rows="2" class="mt-2 w-80" placeholder="{{ __('Optional note') }}" />
                            @else
                                <flux:button wire:click="openFeedback({{ $project->id }})" size="xs" variant="ghost">{{ __('Add note') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <flux:text>{{ __('No projects awaiting verification.') }}</flux:text>
            @endforelse
        </section>
    @endif
</div>