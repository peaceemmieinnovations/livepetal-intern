<?php

namespace App\Policies;

use App\Models\Reflection;
use App\Models\User;

class ReflectionPolicy
{
    /**
     * Reflections are a private journal: only the trainee and administrators.
     * Supervisors never see them.
     */
    public function view(?User $user, Reflection $reflection): bool
    {
        if ($user === null) {
            return false;
        }

        return $reflection->studentProfile->user_id === $user->id
            || $user->isAdmin()
            || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    public function update(User $user, Reflection $reflection): bool
    {
        return $reflection->studentProfile->user_id === $user->id;
    }

    public function delete(User $user, Reflection $reflection): bool
    {
        return $reflection->studentProfile->user_id === $user->id;
    }
}
