<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    /**
     * Published articles are in the open reading experience.
     */
    public function view(?User $user, BlogPost $post): bool
    {
        return true;
    }

    /**
     * Articles are authored and published by staff.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $post->author_id === $user->id || $user->isStaff();
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $post->author_id === $user->id || $user->isStaff();
    }
}
