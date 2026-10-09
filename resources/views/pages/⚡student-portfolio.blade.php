<?php

use App\Enums\Visibility;
use App\Models\StudentProfile;
use Livewire\Component;

new class extends Component
{
    public bool $is_public;

    public bool $show_bio;

    public bool $show_academic;

    public bool $show_skills;

    public bool $show_projects;

    public bool $show_certificates;

    public bool $show_links;

    public function mount(): void
    {
        $profile = $this->profile();

        $this->is_public = (bool) $profile->is_public;
        $this->show_bio = (bool) $profile->show_bio;
        $this->show_academic = (bool) $profile->show_academic;
        $this->show_skills = (bool) $profile->show_skills;
        $this->show_projects = (bool) $profile->show_projects;
        $this->show_certificates = (bool) $profile->show_certificates;
        $this->show_links = (bool) $profile->show_links;
    }

    public function save(): void
    {
        $this->profile()->update([
            'is_public' => $this->is_public,
            'show_bio' => $this->show_bio,
            'show_academic' => $this->show_academic,
            'show_skills' => $this->show_skills,
            'show_projects' => $this->show_projects,
            'show_certificates' => $this->show_certificates,
            'show_links' => $this->show_links,
        ]);

        $this->dispatch('toast-show', heading: __('Portfolio settings saved'), text: $this->is_public ? __('Your profile is now public at the link below.') : __('Your profile is hidden.'), variant: 'success');
    }

    private function profile(): StudentProfile
    {
        $profile = auth()->user()?->studentProfile;

        abort_unless($profile, 404);

        return $profile;
    }

    public function render()
    {
        $profile = $this->profile();

        return $this->view([
            'profile' => $profile,
            'publicUrl' => $profile->user?->username ? route('students.show', $profile->user->username) : null,
            'portfolioProjectCount' => $profile->projects()
                ->where('visibility', Visibility::PublicPortfolio->value)
                ->count(),
            'skillsCount' => $profile->skills()->count(),
            'certificateCount' => $profile->certificates()->count(),
            'hasLink' => filled($profile->github_url) || filled($profile->linkedin_url) || filled($profile->website_url),
        ])->layout('layouts::app')->title(__('Portfolio'));
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1">
        <flux:heading size="2xl">{{ __('Public portfolio') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Control exactly what people who are not signed in can see about you.') }}</flux:text>
    </header>

    <section class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ $is_public ? __('Portfolio is live') : __('Portfolio is private') }}
                </p>
                @if ($publicUrl)
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="mt-1 block text-xs text-sky-600 hover:underline dark:text-sky-400">
                        {{ $publicUrl }}
                    </a>
                @else
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ __('A public link appears once you have a username.') }}</p>
                @endif
            </div>
            <flux:switch wire:model="is_public" />
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Bio') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $profile->bio ? __('Your short introduction') : __('Add a bio to enable this section') }}</p>
                </div>
                <flux:switch wire:model="show_bio" :disabled="! filled($profile->bio)" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Skills') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $skillsCount }} {{ __('recorded') }}</p>
                </div>
                <flux:switch wire:model="show_skills" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Projects') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $portfolioProjectCount }} {{ __('marked public') }}</p>
                </div>
                <flux:switch wire:model="show_projects" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Certificates') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $certificateCount }} {{ __('earned') }}</p>
                </div>
                <flux:switch wire:model="show_certificates" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Academic details') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('Institution, programme and level') }}</p>
                </div>
                <flux:switch wire:model="show_academic" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-zinc-900/10 px-4 py-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Links') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $hasLink ? __('GitHub, LinkedIn, website') : __('Add links to enable this section') }}</p>
                </div>
                <flux:switch wire:model="show_links" :disabled="! $hasLink" />
            </div>
        </div>

        <flux:button wire:click="save" wire:loading.attr="disabled" variant="primary" class="mt-5">
            {{ __('Save portfolio settings') }}
        </flux:button>
    </section>
</div>