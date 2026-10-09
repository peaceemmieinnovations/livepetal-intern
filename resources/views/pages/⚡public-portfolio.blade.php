<?php

use App\Enums\Visibility;
use App\Models\StudentProfile;
use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public function render()
    {
        $profile = StudentProfile::query()
            ->with(['user:id,name,username,avatar_path,email', 'institution', 'programme', 'department', 'skills.skill'])
            ->publicProfiles()
            ->whereHas('user', fn ($query) => $query->where('username', $this->username))
            ->first();

        abort_unless($profile, 404);

        return $this->view([
            'profile' => $profile,
            'projects' => $profile->projects()
                ->where('visibility', Visibility::PublicPortfolio->value)
                ->orderByDesc('updated_at')
                ->get(),
        ])->layout('layouts::public')->title(__(':name — Portfolio', ['name' => $profile->user->name]));
    }
};
?>

<div class="mx-auto w-full max-w-4xl flex-1 px-4 py-10">
    <section class="flex flex-col items-center gap-4 text-center">
        <flux:avatar name="{{ $profile->user->name }}" size="xl" />
        <div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $profile->user->name }}</p>
            @if ($profile->user->email)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $profile->user->email }}</p>
            @endif
            @if ($profile->location)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $profile->location }}</p>
            @endif
        </div>
    </section>

    @if ($profile->show_bio && $profile->bio)
        <section class="mt-8">
            <flux:text class="mx-auto block max-w-2xl text-center">{{ $profile->bio }}</flux:text>
        </section>
    @endif

    @if ($profile->show_skills && $profile->skills->isNotEmpty())
        <section class="mt-8">
            <flux:heading size="lg" class="text-center">{{ __('Skills') }}</flux:heading>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                @foreach ($profile->skills as $entry)
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-zinc-700 shadow-sm dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $entry->skill->name }} · {{ $entry->level->label() }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    @if ($profile->show_projects && $projects->isNotEmpty())
        <section class="mt-8">
            <flux:heading size="lg" class="text-center">{{ __('Projects') }}</flux:heading>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($projects as $project)
                    <article class="rounded-xl border border-zinc-900/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-900">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $project->name }}</p>
                        @if ($project->description)
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ Str::limit($project->description, 180) }}</p>
                        @endif
                        @if ($project->technologies)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($project->technologies as $tech)
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-3 text-xs">
                            @if ($project->repository_url)
                                <a href="{{ $project->repository_url }}" target="_blank" rel="noopener" class="font-medium text-sky-600 hover:underline dark:text-sky-400">{{ __('Source code') }}</a>
                            @endif
                            @if ($project->live_url)
                                <a href="{{ $project->live_url }}" target="_blank" rel="noopener" class="font-medium text-sky-600 hover:underline dark:text-sky-400">{{ __('Live demo') }}</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($profile->show_academic)
        <section class="mt-8">
            <flux:heading size="lg" class="text-center">{{ __('Education') }}</flux:heading>
            <p class="mt-3 text-center text-sm text-zinc-600 dark:text-zinc-300">
                @if ($profile->institution)
                    {{ $profile->institution->name }}
                @endif
                @if ($profile->programme)
                    {{ __('·') }} {{ $profile->programme->name }}
                @endif
                @if ($profile->level)
                    {{ __('· Level') }} {{ $profile->level }}
                @endif
            </p>
        </section>
    @endif

    @if ($profile->show_links)
        <section class="mt-8 flex flex-wrap justify-center gap-3">
            @if ($profile->github_url)
                <a href="{{ $profile->github_url }}" target="_blank" rel="noopener" class="text-sm font-medium text-sky-600 hover:underline dark:text-sky-400">{{ __('GitHub') }}</a>
            @endif
            @if ($profile->linkedin_url)
                <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" class="text-sm font-medium text-sky-600 hover:underline dark:text-sky-400">{{ __('LinkedIn') }}</a>
            @endif
            @if ($profile->website_url)
                <a href="{{ $profile->website_url }}" target="_blank" rel="noopener" class="text-sm font-medium text-sky-600 hover:underline dark:text-sky-400">{{ __('Website') }}</a>
            @endif
        </section>
    @endif
</div>