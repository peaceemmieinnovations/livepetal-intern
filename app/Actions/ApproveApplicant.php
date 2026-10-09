<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Approves a membership application by promoting the member into an intern role
 * and recording who made the decision and when.
 *
 * Approved members gain private trainee access (reports, placements, documents)
 * and are removed from the approval queue.
 */
class ApproveApplicant
{
    public function execute(User $applicant, User $reviewer): User
    {
        abort_unless($applicant->isAwaitingApproval(), 422);

        $applicant->forceFill([
            'role' => UserRole::Student->value,
            'approved_at' => now(),
            'approved_by' => $reviewer->getKey(),
            'rejected_at' => null,
            'rejected_by' => null,
            'decision_note' => null,
        ])->save();

        return $applicant->refresh();
    }
}
