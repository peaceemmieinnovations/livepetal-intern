<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    /**
     * Anyone may see a public portfolio profile; staff and the trainee themself
     * may see the full record.
     */
    public function view(?User $user, StudentProfile $profile): bool
    {
        if ($profile->is_public) {
            return true;
        }

        return $user !== null && $user->canAccessTraineeRecords($profile);
    }

    /**
     * The trainee owns their own profile; administrators may adjust verified
     * academic data during onboarding.
     */
    public function update(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->id || $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * A trainee completes onboarding exactly once.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile === null;
    }

    /**
     * Whether the trainee may decide to publish or unpublish their portfolio.
     */
    public function managePublicProfile(User $user, StudentProfile $profile): bool
    {
        return $profile->user_id === $user->id || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
