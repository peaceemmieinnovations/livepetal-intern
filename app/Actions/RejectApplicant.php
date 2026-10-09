<?php

namespace App\Actions;

use App\Models\User;

/**
 * Turns a membership application down. The applicant keeps their public
 * member access and a reason is stored, but they leave the approval queue.
 */
class RejectApplicant
{
    public function execute(User $applicant, User $reviewer, string $reason): User
    {
        abort_unless($applicant->isAwaitingApproval(), 422);

        $applicant->forceFill([
            'rejected_at' => now(),
            'rejected_by' => $reviewer->getKey(),
            'decision_note' => $reason,
        ])->save();

        return $applicant->refresh();
    }
}
