<?php

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(?User $user, Report $report): bool
    {
        return $user !== null && $user->canAccessTraineeRecords($report->studentProfile);
    }

    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    /**
     * A trainee edits their own draft while it is open, never after it has
     * reached official status.
     */
    public function edit(User $user, Report $report): bool
    {
        return $report->studentProfile->user_id === $user->id
            && in_array($report->status, [ReportStatus::Draft, ReportStatus::RevisionRequested], true);
    }

    /**
     * Moving the draft into the review pipeline is the author's decision.
     */
    public function submit(User $user, Report $report): bool
    {
        return $report->studentProfile->user_id === $user->id
            && in_array($report->status, [ReportStatus::Draft, ReportStatus::RevisionRequested], true);
    }

    /**
     * The assigned supervisor or an administrator reviews and approves.
     */
    public function review(User $user, Report $report): bool
    {
        return $user->isStaff() && $user->canAccessTraineeRecords($report->studentProfile);
    }

    public function finalize(User $user, Report $report): bool
    {
        return $this->review($user, $report) && $report->status !== ReportStatus::Final;
    }

    public function download(User $user, Report $report): bool
    {
        return $this->view($user, $report);
    }
}
