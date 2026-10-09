<?php

namespace App\Policies;

use App\Enums\ActivityStatus;
use App\Models\User;
use App\Models\WeeklySummary;

class WeeklySummaryPolicy
{
    public function view(?User $user, WeeklySummary $weeklySummary): bool
    {
        return $user !== null && $user->canAccessTraineeRecords($weeklySummary->studentProfile);
    }

    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    /**
     * A trainee may keep editing their own summary until it has been published.
     */
    public function update(User $user, WeeklySummary $weeklySummary): bool
    {
        return $weeklySummary->studentProfile->user_id === $user->id
            && $weeklySummary->status !== ActivityStatus::Published;
    }

    public function delete(User $user, WeeklySummary $weeklySummary): bool
    {
        return $weeklySummary->studentProfile->user_id === $user->id
            && $weeklySummary->status === ActivityStatus::Draft;
    }

    /**
     * The assigned supervisor or an administrator reviews the submission.
     */
    public function review(User $user, WeeklySummary $weeklySummary): bool
    {
        return $user->isStaff() && $user->canAccessTraineeRecords($weeklySummary->studentProfile);
    }
}
