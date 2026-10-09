<?php

namespace App\Policies;

use App\Models\BlogComment;
use App\Models\User;

class BlogCommentPolicy
{
    public function view(?User $user, BlogComment $comment): bool
    {
        return true;
    }

    /**
     * Any reader may comment; signed-in comments are published immediately
     * while guest comments wait for moderation.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    public function update(User $user, BlogComment $comment): bool
    {
        return ($comment->user_id !== null && $comment->user_id === $user->id)
            || $user->isStaff();
    }

    public function delete(User $user, BlogComment $comment): bool
    {
        return ($comment->user_id !== null && $comment->user_id === $user->id)
            || $user->isStaff();
    }

    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }
}
