<?php

namespace App\Policies;

use App\Models\Placement;
use App\Models\User;

class PlacementPolicy
{
    /**
     * The trainee, their assigned supervisor and administrators may view a
     * placement.
     */
    public function view(?User $user, Placement $placement): bool
    {
        if ($user === null) {
            return false;
        }

        return $placement->student_id === $user->id
            || $placement->supervisor_id === $user->id
            || $user->isAdmin()
            || $user->isSuperAdmin();
    }

    /**
     * Placements are created and assigned by administrators during onboarding.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Placement $placement): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
