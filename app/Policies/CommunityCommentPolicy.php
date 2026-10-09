<?php

namespace App\Policies;

use App\Models\CommunityComment;
use App\Models\User;

class CommunityCommentPolicy
{
    public function view(?User $user, CommunityComment $comment): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, CommunityComment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isStaff();
    }

    public function delete(User $user, CommunityComment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isStaff();
    }

    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }
}
