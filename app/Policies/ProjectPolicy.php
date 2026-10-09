<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Public-facing projects are visible to everyone; the rest follow trainee
     * record access.
     */
    public function view(?User $user, Project $project): bool
    {
        if ($project->isPubliclyVisible()) {
            return true;
        }

        return $user !== null && $user->canAccessTraineeRecords($project->studentProfile);
    }

    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    public function update(User $user, Project $project): bool
    {
        return $project->studentProfile->user_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->studentProfile->user_id === $user->id;
    }

    /**
     * Verification that a project counts toward the portfolio is an
     * administrator decision.
     */
    public function verify(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
