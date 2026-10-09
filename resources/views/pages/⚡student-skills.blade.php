<?php

use App\Enums\SkillLevel;
use App\Models\Skill;
use App\Models\StudentSkill;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $level = 'beginner';

    public function add(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'level' => ['required', Rule::in(array_keys(SkillLevel::options()))],
        ]);

        $skill = Skill::firstOrCreate(
            ['name' => trim($this->name)],
            ['slug' => Str::slug($this->name), 'category' => 'general', 'is_active' => true],
        );

        $alreadyAdded = StudentSkill::query()
            ->where('student_profile_id', $this->profileId())
            ->where('skill_id', $skill->id)
            ->exists();

        if ($alreadyAdded) {
            $this->addError('name', __('You already added this skill.'));

            return;
        }

        StudentSkill::create([
            'student_profile_id' => $this->profileId(),
            'skill_id' => $skill->id,
            'level' => $this->level,
            'first_practiced_on' => now(),
        ]);

        $this->reset('name');
        $this->level = SkillLevel::Beginner->value;

        $this->dispatch('toast-show', heading: __('Skill added'), text: __('Your skills appear in the report appendices and on your portfolio.'), variant: 'success');
    }

    public function setLevel(int $id, string $level): void
    {
        abort_unless(array_key_exists($level, SkillLevel::options()), 422);

        $entry = StudentSkill::query()
            ->where('student_profile_id', $this->profileId())
            ->findOrFail($id);

        $entry->update(['level' => $level]);
    }

    public function remove(int $id): void
    {
        StudentSkill::query()
            ->where('student_profile_id', $this->profileId())
            ->findOrFail($id)
            ->delete();

        $this->dispatch('toast-show', heading: __('Skill removed'), text: __('The entry was deleted.'), variant: 'success');
    }

    private function profileId(): int
    {
        $profileId = auth()->user()?->studentProfile?->id;

        abort_unless($profileId, 403);

        return $profileId;
    }

    public function render()
    {
        return $this->view([
            'skills' => StudentSkill::query()
                ->with('skill')
                ->where('student_profile_id', $this->profileId())
                ->orderBy('skill_id')
                ->get(),
            'catalog' => Skill::query()->active()->orderBy('name')->get(['id', 'name']),
            'levelOptions' => SkillLevel::options(),
        ])->layout('layouts::app')->title(__('Skills'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header>
        <flux:heading size="2xl">{{ __('Skills') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Record what you practise on placement. Levels are self-assessed and feed your report appendices.') }}</flux:text>
    </header>

    <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
        <form wire:submit="add" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <flux:input wire:model="name" list="skill-catalog" label="{{ __('Skill') }}" placeholder="{{ __('e.g. Laravel, Figma, Excel') }}" :required="true" class="md:col-span-2" />
            <datalist id="skill-catalog">
                @foreach ($catalog as $skill)
                    <option value="{{ $skill->name }}"></option>
                @endforeach
            </datalist>
            <flux:select wire:model="level" label="{{ __('Comfort level') }}">
                @foreach ($levelOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:button type="submit" wire:loading.attr="disabled" variant="primary" class="self-end">{{ __('Add skill') }}</flux:button>
        </form>
        @error('name')
            <flux:error class="mt-2">{{ $message }}</flux:error>
        @enderror
    </section>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($skills as $entry)
            <article wire:key="skill-{{ $entry->id }}" class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $entry->skill->name }}</p>
                        @if ($entry->skill->description)
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $entry->skill->description }}</p>
                        @endif
                        @if ($entry->is_verified)
                            <p class="mt-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">{{ __('Verified by a supervisor') }}</p>
                        @endif
                    </div>
                    <flux:badge :color="[
                        'neutral' => 'zinc',
                        'info' => 'blue',
                        'success' => 'green',
                        'warning' => 'amber',
                    ][$entry->level->badgeVariant()] ?? 'zinc'" size="sm">{{ $entry->level->label() }}</flux:badge>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <flux:select wire:change="setLevel({{ $entry->id }}, $event.target.value)" size="sm" class="max-w-40">
                        @foreach ($levelOptions as $value => $label)
                            <option value="{{ $value }}" @selected($entry->level->value === $value)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:button wire:click="remove({{ $entry->id }})" wire:confirm="{{ __('Remove this skill?') }}" size="sm" variant="ghost">{{ __('Remove') }}</flux:button>
                </div>
            </article>
        @empty
            <x-empty-state
                :title="__('No skills recorded')"
                :body="__('Add the tools and technologies you are practising.' )"
                class="md:col-span-2 lg:col-span-3"
            />
        @endforelse
    </div>
</div>