<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * The public catalogue is open to everyone; only published courses are
     * ever routed.
     */
    public function view(?User $user, Course $course): bool
    {
        return true;
    }

    /**
     * Any signed-up member may start any free course.
     */
    public function enroll(?User $user, Course $course): bool
    {
        return $user !== null;
    }

    /**
     * The catalogue is authored by staff.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isStaff();
    }
}
