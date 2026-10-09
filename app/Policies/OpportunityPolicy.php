<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    /**
     * Curated opportunities are part of the public reading experience.
     */
    public function view(?User $user, Opportunity $opportunity): bool
    {
        return true;
    }

    /**
     * Curating opportunities is an administrator task.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Saving an opportunity is a per-member action.
     */
    public function save(?User $user, Opportunity $opportunity): bool
    {
        return $user !== null;
    }
}
