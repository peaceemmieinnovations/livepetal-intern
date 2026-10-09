<?php

use App\Enums\ActivityStatus;
use App\Enums\Visibility;
use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $activity_date = '';

    public string $title = '';

    public string $description = '';

    public string $learned = '';

    public string $challenge = '';

    public string $resolution = '';

    public string $reflection = '';

    public string $technologies = '';

    public string $external_links = '';

    public string $visibility = 'private';

    public string $project_id = '';

    protected function rules(): array
    {
        return [
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:3000'],
            'learned' => ['nullable', 'string', 'max:2000'],
            'challenge' => ['nullable', 'string', 'max:2000'],
            'resolution' => ['nullable', 'string', 'max:2000'],
            'reflection' => ['nullable', 'string', 'max:2000'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'external_links' => ['nullable', 'url', 'max:255'],
            'visibility' => ['required', Rule::in(array_keys(Visibility::options()))],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('student_profile_id', auth()->user()?->studentProfile?->id)],
        ];
    }

    public function mount(): void
    {
        $this->activity_date = now()->toDateString();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function newEntry(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $log = $this->own($id);
        abort_unless($log->canBeEdited(), 403);

        $this->editingId = $log->id;
        $this->activity_date = $log->activity_date->toDateString();
        $this->title = $log->title;
        $this->description = $log->description ?? '';
        $this->learned = $log->learned ?? '';
        $this->challenge = $log->challenge ?? '';
        $this->resolution = $log->resolution ?? '';
        $this->reflection = $log->reflection ?? '';
        $this->technologies = implode(', ', $log->technologies ?? []);
        $this->external_links = $log->external_links ?? '';
        $this->visibility = $log->visibility->value;
        $this->project_id = $log->project_id ? (string) $log->project_id : '';
        $this->showForm = true;
    }

    public function save(bool $submit = false): void
    {
        $this->validate();

        $data = [
            'activity_date' => $this->activity_date,
            'title' => trim($this->title),
            'description' => trim($this->description) ?: null,
            'learned' => trim($this->learned) ?: null,
            'challenge' => trim($this->challenge) ?: null,
            'resolution' => trim($this->resolution) ?: null,
            'reflection' => trim($this->reflection) ?: null,
            'technologies' => $this->technologyList(),
            'external_links' => trim($this->external_links) ?: null,
            'visibility' => $this->visibility,
            'project_id' => $this->project_id !== '' ? (int) $this->project_id : null,
        ];

        if ($submit) {
            $data['status'] = ActivityStatus::Submitted;
            $data['submitted_at'] = now();
        }

        if ($this->editingId) {
            $log = $this->own($this->editingId);
            abort_unless($log->canBeEdited(), 403);
            $log->update($data);
        } else {
            $data['student_profile_id'] = $this->profileId();
            $data['status'] = $data['status'] ?? ActivityStatus::Draft;
            ActivityLog::create($data);
        }

        $this->resetForm();

        $this->dispatch('toast-show', heading: $submit ? __('Sent for review') : __('Draft saved'), text: $submit ? __('A supervisor will review this entry before it becomes report material.') : __('Complete entries are used by the report manager.'), variant: $submit ? 'success' : 'success');
    }

    public function submitEntry(int $id): void
    {
        $log = $this->own($id);
        abort_unless($log->canBeEdited(), 403);

        $log->update([
            'status' => ActivityStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->dispatch('toast-show', heading: __('Sent for review'), text: __('A supervisor will review this entry.'), variant: 'success');
    }

    public function deleteEntry(int $id): void
    {
        $log = $this->own($id);
        abort_unless($log->isDraft(), 403);

        $log->delete();

        $this->dispatch('toast-show', heading: __('Draft deleted'), text: __('The entry was removed.'), variant: 'success');
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

    private function own(int $id): ActivityLog
    {
        return ActivityLog::query()
            ->where('student_profile_id', $this->profileId())
            ->findOrFail($id);
    }

    private function technologyList(): array
    {
        return collect(preg_split('/[,\r\n]+/', $this->technologies) ?: [])
            ->map(fn (string $tech): string => trim($tech))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'description', 'learned', 'challenge',
            'resolution', 'reflection', 'technologies', 'external_links', 'project_id',
        ]);
        $this->activity_date = now()->toDateString();
        $this->visibility = 'private';
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        $profileId = $this->profileId();

        return $this->view([
            'logs' => ActivityLog::query()
                ->with(['project'])
                ->where('student_profile_id', $profileId)
                ->when($this->filter !== 'all', fn ($query) => $query->where('status', $this->filter))
                ->orderByDesc('activity_date')
                ->orderByDesc('id')
                ->paginate(10),
            'projects' => Project::query()
                ->where('student_profile_id', $profileId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'visibilityOptions' => Visibility::options(),
        ])->layout('layouts::app')->title(__('Activity log'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="2xl">{{ __('Activity log') }}</flux:heading>
            <flux:text class="mt-1 block">{{ __('Daily entries become your report’s chapter three. Once reviewed they are locked and used as source material.') }}</flux:text>
        </div>
        <flux:button wire:click="newEntry" wire:loading.attr="disabled" variant="primary">
            {{ __('Log an activity') }}
        </flux:button>
    </header>

    @if ($showForm)
        <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900" wire:key="form">
            <flux:heading size="lg">{{ $editingId ? __('Edit entry') : __('New entry') }}</flux:heading>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="activity_date" type="date" label="{{ __('Date') }}" :required="true" />
                <flux:select wire:model="project_id" label="{{ __('Project (optional)') }}">
                    <option value="">{{ __('— None —') }}</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="mt-4">
                <flux:input wire:model="title" label="{{ __('What did you do?') }}" placeholder="{{ __('e.g. Built the file upload endpoint') }}" :required="true" />
            </div>

            <div class="mt-4">
                <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="4" :required="true" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:textarea wire:model="learned" label="{{ __('What did you learn?') }}" rows="3" />
                <flux:textarea wire:model="challenge" label="{{ __('Challenge') }}" rows="3" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:textarea wire:model="resolution" label="{{ __('How you solved it') }}" rows="3" />
                <flux:textarea wire:model="reflection" label="{{ __('Reflection') }}" rows="3" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:input wire:model="technologies" label="{{ __('Technologies') }}" placeholder="{{ __('Laravel, MySQL, Livewire') }}" />
                <flux:input wire:model="external_links" type="url" label="{{ __('External link') }}" />
                <flux:select wire:model="visibility" label="{{ __('Visibility') }}">
                    @foreach ($visibilityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>

            @error('activity_date') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('title') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('description') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('technologies') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('external_links') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('visibility') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('project_id') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <flux:button wire:click="save(false)" wire:loading.attr="disabled" variant="primary">{{ __('Save draft') }}</flux:button>
                <flux:button wire:click="save(true)" wire:loading.attr="disabled" variant="ghost">{{ __('Save & submit for review') }}</flux:button>
                <flux:button wire:click="cancel" variant="subtle" :disabled="$showForm === false">{{ __('Cancel') }}</flux:button>
            </div>
        </section>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        @foreach ([
            'all' => __('All'),
            'draft' => __('Drafts'),
            'submitted' => __('Awaiting review'),
            'published' => __('Reviewed'),
            'returned' => __('Returned'),
        ] as $value => $label)
            <flux:button wire:click="$set('filter', '{{ $value }}')" size="sm" :variant="$filter === $value ? 'primary' : 'subtle'">
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($logs as $log)
            <article wire:key="log-{{ $log->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $log->title }}
                            <flux:badge :color="[
                                'neutral' => 'zinc',
                                'warning' => 'amber',
                                'success' => 'green',
                                'danger' => 'red',
                            ][$log->status->badgeVariant()] ?? 'zinc'" size="sm">{{ $log->status->label() }}</flux:badge>
                            <flux:badge color="zinc" variant="subtle" size="sm">{{ $log->visibility->label() }}</flux:badge>
                        </p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            {{ $log->activity_date->format('d M Y') }}
                            @if ($log->project)
                                · {{ $log->project->name }}
                            @endif
                        </p>
                    </div>

                    @if ($log->status->value === 'returned' && $log->reviewer_note)
                        <p class="max-w-sm rounded-lg bg-red-50 px-3 py-2 text-xs text-red-900 dark:bg-red-500/10 dark:text-red-300">{{ $log->reviewer_note }}</p>
                    @endif
                </div>

                @if ($log->description)
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->description }}</p>
                @endif

                @if ($log->technologies)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($log->technologies as $tech)
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $tech }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if ($log->canBeEdited())
                        <flux:button wire:click="edit({{ $log->id }})" size="sm" variant="ghost">{{ __('Edit') }}</flux:button>
                        @if ($log->isDraft())
                            <flux:button wire:click="submitEntry({{ $log->id }})" size="sm" variant="ghost">{{ __('Submit for review') }}</flux:button>
                            <flux:button wire:click="deleteEntry({{ $log->id }})" wire:confirm="{{ __('Delete this draft entry?') }}" size="sm" variant="ghost">{{ __('Delete') }}</flux:button>
                        @else
                            <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500">{{ __('Resubmit after editing to send it back to your supervisor.') }}</flux:text>
                        @endif
                    @else
                        <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500">{{ __('Reviewed and locked. It is now report material.') }}</flux:text>
                    @endif
                </div>
            </article>
        @empty
            <x-empty-state :title="__('No entries here')" :body="__('Log today’s work — the report manager builds chapter three from entries you submit.')" />
        @endforelse
    </div>

    {{ $logs->links() }}
</div>