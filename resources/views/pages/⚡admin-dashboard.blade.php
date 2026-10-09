<?php

use App\Models\CommunityPost;
use App\Models\Course;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Report;
use App\Models\User;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $pendingApprovals = User::query()
            ->pendingApproval()
            ->orderBy('applied_at')
            ->limit(5)
            ->get();

        return $this->view([
            'user' => auth()->user(),
            'pendingApprovals' => $pendingApprovals,
            'pendingCount' => User::query()->pendingApproval()->count(),
            'studentCount' => User::query()->students()->count(),
            'openJobs' => JobPosting::query()->acceptingApplications()->count(),
            'jobApplications' => JobApplication::query()->active()->count(),
            'publishedCourses' => Course::query()->published()->count(),
            'communityPosts' => CommunityPost::query()->count(),
            'reportsToReview' => Report::query()->needingAction()->count(),
            'canReviewApprovals' => Route::has('admin.approvals'),
        ]);
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1">
        <flux:heading size="2xl">{{ __('Administration') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Approvals, training operations and everything published on the platform.') }}</flux:text>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Awaiting approval')" :value="$pendingCount" :hint="__('New sign-ups waiting for access')" />
        <x-stat-card :label="__('Active trainees')" :value="$studentCount" :hint="__('Approved student accounts')" />
        <x-stat-card :label="__('Internships open')" :value="$openJobs" :hint="__('Listings accepting applications')" />
        <x-stat-card :label="__('Applications received')" :value="$jobApplications" :hint="__('Not yet rejected or withdrawn')" />
        <x-stat-card :label="__('Published courses')" :value="$publishedCourses" :hint="__('Free and open to everyone')" />
        <x-stat-card :label="__('Community posts')" :value="$communityPosts" :hint="__('Questions, problems and discussions')" />
        <x-stat-card :label="__('Reports to review')" :value="$reportsToReview" :hint="__('Submitted by trainees')" />
    </div>

    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg">{{ __('Pending approvals') }}</flux:heading>
            @if ($canReviewApprovals)
                <flux:button href="{{ route('admin.approvals') }}" variant="outline" size="sm" wire:navigate>
                    {{ __('Review all') }}
                </flux:button>
            @endif
        </div>

        @if ($pendingApprovals->isEmpty())
            <x-empty-state
                :title="__('Nothing waiting for approval')"
                :body="__('Every account that signs up appears here until you approve it. Approved members are promoted into an intern role and gain access to placements, reports and the portfolio.')"
            />
        @else
            <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                @foreach ($pendingApprovals as $applicant)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $applicant->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $applicant->email }}
                                @if ($applicant->applied_at)
                                    · {{ __('applied :date', ['date' => $applicant->applied_at->format('d M Y')]) }}
                                @endif
                            </p>
                        </div>
                        <flux:badge color="yellow">{{ __('Awaiting review') }}</flux:badge>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
