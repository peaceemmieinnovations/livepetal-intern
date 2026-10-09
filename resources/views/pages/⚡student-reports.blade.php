<?php

use App\Enums\ActivityStatus;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use App\Models\ReportVersion;
use App\Services\ReportBuilder;
use Livewire\Component;

new class extends Component
{
    /** @var array<string, string> section id => body */
    public array $contents = [];

    public function mount(): void
    {
        $this->loadContents();
    }

    private function report(): ?Report
    {
        return auth()->user()?->studentProfile?->reports()->latest('id')->first();
    }

    private function loadContents(): void
    {
        $this->contents = [];

        $report = $this->report();

        if ($report?->currentVersion) {
            foreach ($report->currentVersion->sections as $section) {
                $this->contents[(string) $section->id] = (string) $section->body;
            }
        }
    }

    public function generate(ReportBuilder $builder): void
    {
        $user = auth()->user();
        $profile = $user->studentProfile;

        abort_unless($profile, 404);

        $placement = $user->placement;

        if (! $placement) {
            $this->dispatch('toast-show', heading: __('Placement required'), text: __('A supervisor and training window must be assigned before a draft can be generated.'), variant: 'warning');

            return;
        }

        $report = $profile->reports()->latest('id')->first();

        abort_if($report && ! in_array($report->status, $this->editableStatuses(), true), 403, 'This report is locked while it is in review or approved.');

        $template = ReportTemplate::query()->where('is_default', true)->first() ?? ReportTemplate::query()->first();

        abort_unless($template, 422);

        $report ??= $profile->reports()->create([
            'report_template_id' => $template->id,
            'title' => __('SIWES Report — :name', ['name' => $user->name]),
            'status' => ReportStatus::Draft,
            'current_version' => 0,
        ]);

        $previous = $report->currentVersion;

        $version = ReportVersion::create([
            'report_id' => $report->id,
            'version_number' => $report->current_version + 1,
            'status' => $report->status,
            'generated_by' => $user->id,
            'generated_at' => now(),
            'source_snapshot' => $this->sourceSnapshot($profile),
            'generation_notes' => __('Generated from your recorded activities, projects, skills and weekly summaries.'),
        ]);

        $edited = $previous
            ? $previous->sections->filter(fn (ReportSection $section): bool => $section->is_edited)->keyBy('type')
            : collect();

        foreach ($builder->sections($user, $template) as $index => $section) {
            $body = $section['body'];

            if ($existing = $edited->get($section['type']->value)) {
                $body = (string) $existing->body;
            }

            ReportSection::create([
                'report_version_id' => $version->id,
                'type' => $section['type']->value,
                'heading' => $section['type']->heading(),
                'subheading' => $section['type']->label(),
                'body' => $body,
                'placeholder_note' => $section['placeholder_note'],
                'is_edited' => $existing !== null,
                'sort_order' => $index,
            ]);
        }

        $report->update([
            'status' => $report->status,
            'current_version' => $version->version_number,
            'current_version_id' => $version->id,
        ]);

        $this->loadContents();

        $this->dispatch('toast-show', heading: $previous ? __('Draft refreshed') : __('Draft generated'), text: __('The draft is built only from records you have saved. Review every section before submitting.'), variant: 'success');
    }

    public function saveSection(int $sectionId): void
    {
        $report = $this->report();

        abort_unless($report && in_array($report->status, $this->editableStatuses(), true), 403);

        $section = ReportSection::query()
            ->where('report_version_id', $report->current_version_id)
            ->findOrFail($sectionId);

        $validated = $this->validate([
            "contents.{$sectionId}" => ['nullable', 'string', 'max:60000'],
        ]);

        $section->update([
            'body' => $validated['contents'][$sectionId] ?? '',
            'is_edited' => true,
            'edited_by' => auth()->id(),
            'edited_at' => now(),
        ]);

        $this->dispatch('toast-show', heading: __('Section saved'), text: $section->subheading, variant: 'success');
    }

    public function requestReview(): void
    {
        $report = $this->report();

        abort_unless($report && in_array($report->status, $this->editableStatuses(), true), 403);

        $report->update([
            'status' => ReportStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->dispatch('toast-show', heading: __('Sent for review'), text: __('Your supervisor has been notified to review the draft.'), variant: 'success');
    }

    private function editableStatuses(): array
    {
        return [ReportStatus::Draft, ReportStatus::RevisionRequested];
    }

    private function isLocked(Report $report): bool
    {
        return ! in_array($report->status, $this->editableStatuses(), true);
    }

    /**
     * @return array<string, int>
     */
    private function sourceSnapshot($profile): array
    {
        return [
            'activities' => (int) $profile->activities()->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])->count(),
            'projects' => (int) $profile->projects()->count(),
            'skills' => (int) $profile->skills()->count(),
            'weekly_summaries' => (int) $profile->weeklySummaries()->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])->count(),
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        $placement = $user->placement;
        $report = $this->report();

        $submittedActivities = $profile?->activities()
            ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])
            ->count() ?? 0;

        $weeklySubmitted = $profile?->weeklySummaries()
            ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])
            ->count() ?? 0;

        $checks = [
            [
                'label' => __('Placement and supervisor assigned'),
                'done' => $placement !== null && $placement->supervisor_id !== null,
                'route' => 'student.projects.index',
                'hint' => null,
            ],
            [
                'label' => __('Daily activities logged and submitted'),
                'done' => $submittedActivities > 0,
                'route' => 'student.activities.index',
                'hint' => $submittedActivities.' '.__('found'),
            ],
            [
                'label' => __('Projects documented'),
                'done' => ($profile?->projects()->count() ?? 0) > 0,
                'route' => 'student.projects.index',
                'hint' => null,
            ],
            [
                'label' => __('Skills recorded'),
                'done' => ($profile?->skills()->count() ?? 0) > 0,
                'route' => 'student.skills',
                'hint' => null,
            ],
            [
                'label' => __('Weekly summaries submitted'),
                'done' => $weeklySubmitted > 0,
                'route' => 'student.projects.index',
                'hint' => $weeklySubmitted.' '.__('found'),
            ],
        ];

        return $this->view([
            'user' => $user,
            'profile' => $profile,
            'report' => $report,
            'sections' => $report?->currentVersion?->sections ?? collect(),
            'locked' => $report ? $this->isLocked($report) : false,
            'checks' => $checks,
        ])->layout('layouts::app')->title(__('Report manager'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header>
        <flux:heading size="2xl">{{ __('Report manager') }}</flux:heading>
        <flux:text class="mt-1 block">
            {{ __('The draft is built only from records you have already saved. Nothing is invented. Text in [square brackets] is for you to write.') }}
        </flux:text>
    </header>

    <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('What the draft can use') }}</flux:heading>
        <ul class="mt-4 space-y-3">
            @foreach ($checks as $check)
                <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex items-center gap-2">
                        <span class="inline-block size-2.5 rounded-full {{ $check['done'] ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                        <span class="{{ $check['done'] ? 'text-zinc-700 dark:text-zinc-200' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $check['label'] }}</span>
                        @if ($check['hint'])
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">({{ $check['hint'] }})</span>
                        @endif
                    </span>
                    @if (! $check['done'] && Route::has($check['route']))
                        <flux:button :href="route($check['route'])" variant="ghost" size="xs" wire:navigate>{{ __('Add') }}</flux:button>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <div class="flex flex-wrap items-center gap-2">
        @if (! $locked)
            <flux:button wire:click="generate" wire:loading.attr="disabled" variant="primary">
                {{ $report ? __('Refresh from my records') : __('Generate draft') }}
            </flux:button>
        @endif

        @if ($report)
            @if (in_array($report->status->value, ['draft', 'revision_requested'], true))
                <flux:button wire:click="requestReview" wire:confirm="{{ __('Send this report to your supervisor? You will not be able to edit it while it is in review.') }}" variant="ghost">
                    {{ __('Request review') }}
                </flux:button>
            @endif
            <flux:button :href="route('reports.print', $report)" target="_blank" variant="ghost">
                {{ __('Print / save as PDF') }}
            </flux:button>
            <flux:badge :color="[
                'success' => 'green',
                'warning' => 'yellow',
                'danger' => 'red',
                'info' => 'blue',
                'neutral' => 'zinc',
            ][$report->status->badgeVariant()] ?? 'zinc'">
                {{ $report->status->label() }}
            </flux:badge>
            <flux:text size="sm">{{ __('Version :version', ['version' => $report->current_version]) }}</flux:text>
        @endif
    </div>

    @if ($report && $report->status->value === 'revision_requested' && $report->latestVersion()?->reviews()->latest('id')->first()?->comments)
        <p class="rounded-xl bg-yellow-50 px-4 py-3 text-sm text-yellow-900 dark:bg-yellow-500/10 dark:text-yellow-300">
            <span class="font-semibold">{{ __('Supervisor feedback:') }}</span>
            {{ $report->latestVersion()->reviews()->latest('id')->first()->comments }}
        </p>
    @endif

    @if ($report && ! $locked)
        <flux:text size="sm">{{ __('"Refresh from my records" updates sections you have not edited. Sections you edited are never overwritten.') }}</flux:text>
    @endif

    @forelse ($sections as $section)
        <section wire:key="section-{{ $section->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-base font-semibold text-zinc-900 dark:text-white">{{ $section->heading }}</p>
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">{{ $section->subheading }}</p>
                </div>
                @php
                    $label = $section->placeholder_note
                        ?? ($section->is_edited ? __('Edited by you') : __('Generated from records'));
                @endphp
                <flux:badge color="{{ $section->is_edited ? 'sky' : ($section->placeholder_note ? 'amber' : 'emerald') }}" variant="subtle">
                    {{ $label }}
                </flux:badge>
            </div>

            <flux:textarea
                wire:model="contents.{{ $section->id }}"
                rows="{{ min(18, max(5, substr_count((string) $section->body, "\n") + 3)) }}"
                class="mt-3"
                :disabled="$locked"
            />
            @error("contents.{$section->id}")
                <flux:error class="mt-1">{{ $message }}</flux:error>
            @enderror

            @unless ($locked)
                <flux:button wire:click="saveSection({{ $section->id }})" variant="ghost" size="sm" class="mt-2">
                    {{ __('Save section') }}
                </flux:button>
            @endunless
        </section>
    @empty
        <x-empty-state
            :title="__('No draft yet')"
            :body="__('Generate a draft once your placement is assigned. The more you have logged, the fuller it will be.')"
        />
    @endforelse
</div>