<?php

namespace App\Policies;

use App\Enums\ActivityStatus;
use App\Models\MonthlySummary;
use App\Models\User;

class MonthlySummaryPolicy
{
    public function view(?User $user, MonthlySummary $monthlySummary): bool
    {
        return $user !== null && $user->canAccessTraineeRecords($monthlySummary->studentProfile);
    }

    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    public function update(User $user, MonthlySummary $monthlySummary): bool
    {
        return $monthlySummary->studentProfile->user_id === $user->id
            && $monthlySummary->status !== ActivityStatus::Published;
    }

    public function delete(User $user, MonthlySummary $monthlySummary): bool
    {
        return $monthlySummary->studentProfile->user_id === $user->id
            && $monthlySummary->status === ActivityStatus::Draft;
    }

    public function review(User $user, MonthlySummary $monthlySummary): bool
    {
        return $user->isStaff() && $user->canAccessTraineeRecords($monthlySummary->studentProfile);
    }
}
