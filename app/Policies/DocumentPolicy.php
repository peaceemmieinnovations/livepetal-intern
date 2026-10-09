<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Documents are private. The owner, their supervisor and administrators may
     * read or download them.
     */
    public function view(?User $user, Document $document): bool
    {
        if ($user === null) {
            return false;
        }

        return $document->user_id === $user->id || $user->canAccessTraineeRecords($document->user);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $document->user_id === $user->id || $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Official verification of letters, certificates and ID documents.
     */
    public function verify(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
