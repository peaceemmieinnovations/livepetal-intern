<?php

namespace App\Policies;

use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentPolicy
{
    public function view(User $user, CourseEnrollment $enrollment): bool
    {
        return $enrollment->user_id === $user->id;
    }

    /**
     * Enrolling is open to every signed-in member and creates no data until
     * they choose a course.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Progress is tracked against the learner's own enrolment.
     */
    public function resume(User $user, CourseEnrollment $enrollment): bool
    {
        return $enrollment->user_id === $user->id;
    }

    public function complete(User $user, CourseEnrollment $enrollment): bool
    {
        return $enrollment->user_id === $user->id;
    }

    public function delete(User $user, CourseEnrollment $enrollment): bool
    {
        return $enrollment->user_id === $user->id || $user->isStaff();
    }
}
