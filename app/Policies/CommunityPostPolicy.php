<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
{
    public function view(?User $user, CommunityPost $post): bool
    {
        return true;
    }

    /**
     * Signed-in members contribute to the community feed.
     */
    public function create(?User $user): bool
    {
        return $user !== null;
    }

    /**
     * The author, staff and administrators may edit a post.
     */
    public function update(User $user, CommunityPost $post): bool
    {
        return $post->user_id === $user->id || $user->isStaff();
    }

    public function delete(User $user, CommunityPost $post): bool
    {
        return $post->user_id === $user->id || $user->isStaff();
    }

    /**
     * A problem or question can be marked solved by its author or by staff.
     */
    public function solve(User $user, CommunityPost $post): bool
    {
        return $post->isAnswerable()
            && ($post->user_id === $user->id || $user->isStaff());
    }

    public function unsolve(User $user, CommunityPost $post): bool
    {
        return $post->user_id === $user->id || $user->isStaff();
    }

    /**
     * Pinning and moderation are staff decisions.
     */
    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }
}
