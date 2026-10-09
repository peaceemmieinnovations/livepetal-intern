<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    /**
     * The trainee who owns the record, their supervisor and administrators may
     * read it.
     */
    public function view(?User $user, ActivityLog $activityLog): bool
    {
        return $user !== null && $user->canAccessTraineeRecords($activityLog->studentProfile);
    }

    /**
     * Only approved trainees log their own work.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    /**
     * A trainee may edit their own entry while it is not yet published.
     */
    public function update(User $user, ActivityLog $activityLog): bool
    {
        return $activityLog->studentProfile->user_id === $user->id && $activityLog->canBeEdited();
    }

    /**
     * A trainee may delete an entry that has never left their control.
     */
    public function delete(User $user, ActivityLog $activityLog): bool
    {
        return $activityLog->studentProfile->user_id === $user->id && $activityLog->isDraft();
    }

    /**
     * Supervisors review the entries of their own trainees; administrators may
     * review any entry.
     */
    public function review(User $user, ActivityLog $activityLog): bool
    {
        return $user->isStaff() && $user->canAccessTraineeRecords($activityLog->studentProfile);
    }
}
