<?php

namespace App\Policies;

use App\Models\KnowledgeResource;
use App\Models\User;

class KnowledgeResourcePolicy
{
    /**
     * Approved resources are public.
     */
    public function view(?User $user, KnowledgeResource $resource): bool
    {
        return true;
    }

    /**
     * Any trainee or staff member may contribute a resource for review.
     */
    public function create(?User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, KnowledgeResource $resource): bool
    {
        return ($resource->student_profile_id !== null
            && $resource->student_profile_id === $user->studentProfile?->id)
            || $user->isStaff();
    }

    public function delete(User $user, KnowledgeResource $resource): bool
    {
        return $user->isStaff();
    }

    /**
     * Publishing a contribution into the shared knowledge base is a review
     * decision made by staff.
     */
    public function review(User $user): bool
    {
        return $user->isStaff();
    }
}
