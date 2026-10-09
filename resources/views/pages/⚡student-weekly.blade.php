<?php

use App\Enums\ActivityStatus;
use App\Models\WeeklySummary;
use Illuminate\Support\Carbon;
use Livewire\Component;

new class extends Component
{
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $week_starts_on = '';

    public string $summary = '';

    public string $major_learning = '';

    public string $challenges = '';

    public string $achievements = '';

    public string $next_week_goals = '';

    protected function rules(): array
    {
        return [
            'week_starts_on' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:4000'],
            'major_learning' => ['nullable', 'string', 'max:2000'],
            'challenges' => ['nullable', 'string', 'max:2000'],
            'achievements' => ['nullable', 'string', 'max:2000'],
            'next_week_goals' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function mount(): void
    {
        $this->week_starts_on = now()->startOfWeek()->toDateString();
    }

    public function newWeek(): void
    {
        $this->resetForm();
        $this->week_starts_on = now()->startOfWeek()->toDateString();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $week = $this->own($id);
        abort_unless($week->status !== ActivityStatus::Published, 403);

        $this->editingId = $week->id;
        $this->week_starts_on = $week->week_starts_on->toDateString();
        $this->summary = $week->summary ?? '';
        $this->major_learning = $week->major_learning ?? '';
        $this->challenges = $week->challenges ?? '';
        $this->achievements = $week->achievements ?? '';
        $this->next_week_goals = $week->next_week_goals ?? '';
        $this->showForm = true;
    }

    public function save(bool $submit = false): void
    {
        $this->validate();

        $startsOn = Carbon::parse($this->week_starts_on)->startOfDay();

        $overlaps = WeeklySummary::query()
            ->where('student_profile_id', $this->profileId())
            ->where('week_starts_on', $startsOn)
            ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
            ->exists();

        if ($overlaps) {
            $this->addError('week_starts_on', __('You already have a summary for this week.'));

            return;
        }

        $data = [
            'week_starts_on' => $startsOn->toDateString(),
            'week_ends_on' => $startsOn->addDays(6)->toDateString(),
            'summary' => trim($this->summary),
            'major_learning' => trim($this->major_learning) ?: null,
            'challenges' => trim($this->challenges) ?: null,
            'achievements' => trim($this->achievements) ?: null,
            'next_week_goals' => trim($this->next_week_goals) ?: null,
        ];

        if ($submit) {
            $data['status'] = ActivityStatus::Submitted;
            $data['submitted_at'] = now();
        }

        if ($this->editingId) {
            $week = $this->own($this->editingId);
            abort_unless($week->status !== ActivityStatus::Published, 403);
            $week->update($data);
        } else {
            $data['student_profile_id'] = $this->profileId();
            $data['status'] = $data['status'] ?? ActivityStatus::Draft;
            WeeklySummary::create($data);
        }

        $this->resetForm();

        $this->dispatch('toast-show', heading: $submit ? __('Sent for review') : __('Draft saved'), text: $submit ? __('Your supervisor will confirm this week.') : __('Summaries support your report.'), variant: 'success');
    }

    public function submitWeek(int $id): void
    {
        $week = $this->own($id);
        abort_unless($week->status !== ActivityStatus::Published, 403);

        $week->update([
            'status' => ActivityStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->dispatch('toast-show', heading: __('Sent for review'), text: __('Your supervisor can now approve this week.'), variant: 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function profileId(): int
    {
        $profileId = auth()->user()?->studentProfile?->id;

        abort_unless($profileId, 403);

        return $profileId;
    }

    private function own(int $id): WeeklySummary
    {
        return WeeklySummary::query()
            ->where('student_profile_id', $this->profileId())
            ->findOrFail($id);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'summary', 'major_learning', 'challenges', 'achievements', 'next_week_goals']);
        $this->week_starts_on = now()->startOfWeek()->toDateString();
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'weeks' => WeeklySummary::query()
                ->where('student_profile_id', $this->profileId())
                ->orderByDesc('week_starts_on')
                ->get(),
        ])->layout('layouts::app')->title(__('Weekly summaries'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="2xl">{{ __('Weekly summaries') }}</flux:heading>
            <flux:text class="mt-1 block">{{ __('Write one short week-in-review per week. Your supervisor’s approval counts towards your completion.') }}</flux:text>
        </div>
        <flux:button wire:click="newWeek" wire:loading.attr="disabled" variant="primary">
            {{ __('Write this week') }}
        </flux:button>
    </header>

    @if ($showForm)
        <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900" wire:key="week-form">
            <flux:heading size="lg">{{ $editingId ? __('Edit week') : __('Weekly summary') }}</flux:heading>

            <div class="mt-4">
                <flux:input wire:model="week_starts_on" type="date" label="{{ __('Week starting') }}" :required="true" />
            </div>

            <div class="mt-4">
                <flux:textarea wire:model="summary" label="{{ __('What did the week cover?') }}" rows="4" :required="true" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:textarea wire:model="major_learning" label="{{ __('Biggest learning') }}" rows="3" />
                <flux:textarea wire:model="challenges" label="{{ __('Challenges') }}" rows="3" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:textarea wire:model="achievements" label="{{ __('Achievements') }}" rows="3" />
                <flux:textarea wire:model="next_week_goals" label="{{ __('Goals for next week') }}" rows="3" />
            </div>

            @error('week_starts_on') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('summary') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <flux:button wire:click="save(false)" wire:loading.attr="disabled" variant="primary">{{ __('Save draft') }}</flux:button>
                <flux:button wire:click="save(true)" wire:loading.attr="disabled" variant="ghost">{{ __('Save & submit for review') }}</flux:button>
                <flux:button wire:click="cancel" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </section>
    @endif

    <div class="flex flex-col gap-4">
        @forelse ($weeks as $week)
            <article wire:key="week-{{ $week->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $week->week_starts_on->format('d M Y') }} — {{ $week->week_ends_on->format('d M Y') }}
                            <flux:badge :color="[
                                'neutral' => 'zinc',
                                'warning' => 'amber',
                                'success' => 'green',
                                'danger' => 'red',
                            ][$week->status->badgeVariant()] ?? 'zinc'" size="sm">{{ $week->status->label() }}</flux:badge>
                        </p>
                        @if ($week->status->value === 'returned' && $week->reviewer_note)
                            <p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-900 dark:bg-red-500/10 dark:text-red-300">{{ $week->reviewer_note }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($week->status !== \App\Enums\ActivityStatus::Published)
                            <flux:button wire:click="edit({{ $week->id }})" size="sm" variant="ghost">{{ __('Edit') }}</flux:button>
                            @if ($week->status === \App\Enums\ActivityStatus::Draft)
                                <flux:button wire:click="submitWeek({{ $week->id }})" size="sm" variant="ghost">{{ __('Submit for review') }}</flux:button>
                            @endif
                        @else
                            <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500">{{ __('Approved') }}</flux:text>
                        @endif
                    </div>
                </div>

                @if ($week->summary)
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ Str::limit($week->summary, 240) }}</p>
                @endif
            </article>
        @empty
            <x-empty-state
                :title="__('No weeks written yet')"
                :body="__('Write a short review of each week so your supervisor can confirm your progress.')"
            />
        @endforelse
    </div>
</div>