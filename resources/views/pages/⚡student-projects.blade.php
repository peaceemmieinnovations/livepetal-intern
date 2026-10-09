<?php

use App\Enums\ProjectStatus;
use App\Enums\Visibility;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $description = '';

    public string $problem = '';

    public string $solution = '';

    public string $role = '';

    public string $technologies = '';

    public string $status = 'in_progress';

    public string $visibility = 'private';

    public string $repository_url = '';

    public string $live_url = '';

    public string $starts_on = '';

    public string $ends_on = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'problem' => ['nullable', 'string', 'max:3000'],
            'solution' => ['nullable', 'string', 'max:3000'],
            'role' => ['nullable', 'string', 'max:150'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(ProjectStatus::options()))],
            'visibility' => ['required', Rule::in(array_keys(Visibility::options()))],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ];
    }

    public function newProject(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $project = $this->own($id);

        $this->editingId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description ?? '';
        $this->problem = $project->problem ?? '';
        $this->solution = $project->solution ?? '';
        $this->role = $project->role ?? '';
        $this->technologies = implode(', ', $project->technologies ?? []);
        $this->status = $project->status->value;
        $this->visibility = $project->visibility->value;
        $this->repository_url = $project->repository_url ?? '';
        $this->live_url = $project->live_url ?? '';
        $this->starts_on = $project->starts_on?->toDateString() ?? '';
        $this->ends_on = $project->ends_on?->toDateString() ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => trim($this->name),
            'description' => trim($this->description) ?: null,
            'problem' => trim($this->problem) ?: null,
            'solution' => trim($this->solution) ?: null,
            'role' => trim($this->role) ?: null,
            'technologies' => $this->technologyList(),
            'status' => $this->status,
            'visibility' => $this->visibility,
            'repository_url' => trim($this->repository_url) ?: null,
            'live_url' => trim($this->live_url) ?: null,
            'starts_on' => $this->starts_on ?: null,
            'ends_on' => $this->ends_on ?: null,
        ];

        if ($this->editingId) {
            $this->own($this->editingId)->update($data);
        } else {
            $data['student_profile_id'] = $this->profileId();
            $data['slug'] = Str::slug($this->name).'-'.Str::lower(Str::random(4));

            Project::create($data);
        }

        $this->resetForm();

        $this->dispatch('toast-show', heading: __('Project saved'), text: __('Documented projects feed the report manager’s chapter four and your portfolio.'), variant: 'success');
    }

    public function togglePortfolio(int $id): void
    {
        $project = $this->own($id);

        $project->update([
            'visibility' => $project->visibility === Visibility::PublicPortfolio
                ? Visibility::Private
                : Visibility::PublicPortfolio,
        ]);

        $this->dispatch('toast-show', heading: $project->refresh()->visibility === Visibility::PublicPortfolio ? __('Shown on portfolio') : __('Hidden from portfolio'), text: __('Visibility applies only when your profile is public.'), variant: 'success');
    }

    public function delete(int $id): void
    {
        $this->own($id)->delete();

        $this->dispatch('toast-show', heading: __('Project deleted'), text: __('The project record was removed.'), variant: 'success');
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

    private function own(int $id): Project
    {
        return Project::query()
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
            'editingId', 'name', 'description', 'problem', 'solution', 'role',
            'technologies', 'repository_url', 'live_url', 'starts_on', 'ends_on',
        ]);
        $this->status = ProjectStatus::InProgress->value;
        $this->visibility = Visibility::Private->value;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'projects' => Project::query()
                ->where('student_profile_id', $this->profileId())
                ->orderByDesc('updated_at')
                ->get(),
            'statusOptions' => ProjectStatus::options(),
            'visibilityOptions' => Visibility::options(),
        ])->layout('layouts::app')->title(__('Projects'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="2xl">{{ __('Projects') }}</flux:heading>
            <flux:text class="mt-1 block">{{ __('Document your work for chapter four of your report and for your public portfolio.') }}</flux:text>
        </div>
        <flux:button wire:click="newProject" wire:loading.attr="disabled" variant="primary">
            {{ __('Add a project') }}
        </flux:button>
    </header>

    @if ($showForm)
        <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900" wire:key="project-form">
            <flux:heading size="lg">{{ $editingId ? __('Edit project') : __('New project') }}</flux:heading>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="name" label="{{ __('Project name') }}" :required="true" placeholder="{{ __('e.g. SIWES attendance tracker') }}" />
                <flux:input wire:model="role" label="{{ __('Your role') }}" placeholder="{{ __('e.g. Backend developer') }}" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:select wire:model="status" label="{{ __('Status') }}">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="visibility" label="{{ __('Visibility') }}">
                    @foreach ($visibilityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="technologies" label="{{ __('Technologies') }}" placeholder="{{ __('Laravel, MySQL, Livewire') }}" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:input wire:model="starts_on" type="date" label="{{ __('Started on') }}" />
                <flux:input wire:model="ends_on" type="date" label="{{ __('Finished on') }}" />
                <flux:input wire:model="repository_url" type="url" label="{{ __('Repository URL') }}" />
            </div>

            <div class="mt-4">
                <flux:input wire:model="live_url" type="url" label="{{ __('Live URL') }}" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4">
                <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="3" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:textarea wire:model="problem" label="{{ __('Problem it solved') }}" rows="3" />
                <flux:textarea wire:model="solution" label="{{ __('How you solved it') }}" rows="3" />
            </div>

            @error('name') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror
            @error('ends_on') <flux:error class="mt-1">{{ $message }}</flux:error> @enderror

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <flux:button wire:click="save" wire:loading.attr="disabled" variant="primary">{{ __('Save project') }}</flux:button>
                <flux:button wire:click="cancel" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </section>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse ($projects as $project)
            <article wire:key="project-{{ $project->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $project->name }}
                            <flux:badge :color="[
                                'neutral' => 'zinc',
                                'info' => 'blue',
                                'success' => 'green',
                            ][$project->status->badgeVariant()] ?? 'zinc'" size="sm">{{ $project->status->label() }}</flux:badge>
                            @if ($project->visibility === \App\Enums\Visibility::PublicPortfolio)
                                <flux:badge color="sky" variant="subtle" size="sm">{{ __('On portfolio') }}</flux:badge>
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            @if ($project->starts_on)
                                {{ $project->starts_on->format('M Y') }}{{ $project->ends_on ? ' – '.$project->ends_on->format('M Y') : __(' – present') }}
                            @endif
                        </p>
                    </div>
                </div>

                @if ($project->description)
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $project->description }}</p>
                @endif

                @if ($project->technologies)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($project->technologies as $tech)
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $tech }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-400 dark:text-zinc-500">
                        @if ($project->repository_url)
                            <a href="{{ $project->repository_url }}" target="_blank" rel="noopener" class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Code') }}</a>
                        @endif
                        @if ($project->live_url)
                            <a href="{{ $project->live_url }}" target="_blank" rel="noopener" class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Live') }}</a>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button wire:click="togglePortfolio({{ $project->id }})" size="sm" variant="ghost">
                            {{ $project->visibility === \App\Enums\Visibility::PublicPortfolio ? __('Hide') : __('Show on portfolio') }}
                        </flux:button>
                        <flux:button wire:click="edit({{ $project->id }})" size="sm" variant="ghost">{{ __('Edit') }}</flux:button>
                        <flux:button wire:click="delete({{ $project->id }})" wire:confirm="{{ __('Delete this project?') }}" size="sm" variant="ghost">{{ __('Delete') }}</flux:button>
                    </div>
                </div>

                @if ($project->review_status === \App\Enums\ReviewStatus::Verified)
                    <p class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-300">{{ __('Verified by a supervisor') }}</p>
                @endif
            </article>
        @empty
            <x-empty-state
                :title="__('No projects yet')"
                :body="__('Add the work you have done. Projects you finish and mark public appear on your portfolio.')"
                class="lg:col-span-2"
            />
        @endforelse
    </div>
</div>