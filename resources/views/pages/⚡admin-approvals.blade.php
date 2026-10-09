<?php

use App\Actions\ApproveApplicant;
use App\Actions\RejectApplicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

new class extends Component
{
    public ?int $rejectingApplicantId = null;

    public string $rejectNote = '';

    public function approve(int $applicantId): void
    {
        $this->authorize('approve-members');

        $applicant = User::query()->findOrFail($applicantId);

        app(ApproveApplicant::class)->execute($applicant, auth()->user());

        $this->dispatch('toast-show', heading: __('Applicant approved'), text: __(':name now has intern access.', ['name' => $applicant->name]), variant: 'success');
    }

    public function beginReject(int $applicantId): void
    {
        $this->authorize('approve-members');

        $this->rejectingApplicantId = $applicantId;
        $this->rejectNote = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingApplicantId = null;
        $this->rejectNote = '';
    }

    public function reject(): void
    {
        $this->authorize('approve-members');

        $validated = $this->validate([
            'rejectNote' => ['required', 'string', 'min:3', 'max:400'],
        ]);

        $applicant = User::query()->findOrFail($this->rejectingApplicantId);

        app(RejectApplicant::class)->execute($applicant, auth()->user(), $validated['rejectNote']);

        $this->cancelReject();

        $this->dispatch('toast-show', heading: __('Application rejected'), text: __(':name was turned down.', ['name' => $applicant->name]), variant: 'success');
    }

    public function render()
    {
        $pendingApprovals = User::query()
            ->pendingApproval()
            ->get();

        $recentlyDecided = User::query()
            ->where(function (Builder $query): void {
                $query->whereNotNull('approved_at')->orWhereNotNull('rejected_at');
            })
            ->where('updated_at', '>=', now()->subDays(14))
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return $this->view([
            'pendingApprovals' => $pendingApprovals,
            'recentlyDecided' => $recentlyDecided,
            'awaitingCount' => $pendingApprovals->count(),
            'decidedRecentlyCount' => $recentlyDecided->count(),
        ]);
    }
};
?>

<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-1">
        <flux:heading size="2xl">{{ __('Membership approvals') }}</flux:heading>
        <flux:text class="mt-1 block">{{ __('Review every account that signs up before it gains intern access.') }}</flux:text>
    </header>

    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg">{{ __('Awaiting review') }}</flux:heading>
            <flux:badge color="yellow">{{ trans_choice('{0} none pending|{1} :count application|[2,*] :count applications', $awaitingCount) }}</flux:badge>
        </div>

        @if ($pendingApprovals->isEmpty())
            <x-empty-state
                :title="__('Nothing waiting for approval')"
                :body="__('Every account that signs up appears here until you approve or reject it. Approved members are promoted into an intern role and unlock placements, reports and the portfolio.')"
            />
        @else
            <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                @foreach ($pendingApprovals as $applicant)
                    <div class="flex flex-col gap-4 px-5 py-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $applicant->name }}</p>
                                <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $applicant->email }}
                                    @if ($applicant->applied_at)
                                        · {{ __('applied :date', ['date' => $applicant->applied_at->format('d M Y')]) }}
                                    @endif
                                </p>

                                @if ($applicant->application_note)
                                    <p class="mt-2 max-w-xl text-sm text-zinc-600 dark:text-zinc-300">
                                        “{{ $applicant->application_note }}”
                                    </p>
                                @endif
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <flux:button
                                    size="sm"
                                    color="emerald"
                                    variant="filled"
                                    wire:click="approve({{ $applicant->id }})"
                                    wire:loading.attr="disabled"
                                    data-test="approve-applicant"
                                    data-applicant-id="{{ $applicant->id }}"
                                >
                                    {{ __('Approve') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="beginReject({{ $applicant->id }})"
                                    wire:loading.attr="disabled"
                                    data-test="reject-applicant"
                                    data-applicant-id="{{ $applicant->id }}"
                                >
                                    {{ __('Reject') }}
                                </flux:button>
                            </div>
                        </div>

                        @if ($rejectingApplicantId === $applicant->id)
                            <flux:field class="max-w-xl">
                                <flux:label>{{ __('Reason for rejection') }}</flux:label>
                                <flux:textarea
                                    wire:model="rejectNote"
                                    rows="2"
                                    :placeholder="__('What should this applicant know?')"
                                    data-test="reject-note"
                                />
                                <flux:error name="rejectNote" />
                            </flux:field>

                            <div class="flex items-center gap-2">
                                <flux:button
                                    size="sm"
                                    color="red"
                                    variant="filled"
                                    wire:click="reject"
                                    wire:loading.attr="disabled"
                                    data-test="confirm-reject"
                                >
                                    {{ __('Confirm rejection') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" wire:click="cancelReject">
                                    {{ __('Cancel') }}
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if ($recentlyDecided->isNotEmpty())
        <section class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Recently reviewed') }}</flux:heading>

            <div class="divide-y divide-zinc-900/10 overflow-hidden rounded-xl border border-zinc-900/10 bg-white shadow-sm dark:divide-white/10 dark:border-white/10 dark:bg-zinc-900">
                @foreach ($recentlyDecided as $applicant)
                    <div class="flex items-start justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $applicant->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $applicant->email }}
                                @if ($applicant->decision_note)
                                    · {{ $applicant->decision_note }}
                                @endif
                            </p>
                        </div>
                        @if ($applicant->isRejected())
                            <flux:badge color="red" class="shrink-0">{{ __('Rejected') }}</flux:badge>
                        @else
                            <flux:badge color="emerald" class="shrink-0">{{ __('Approved') }}</flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>