<?php

namespace App\Actions;

use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use RuntimeException;

/**
 * Issues the certificate earned by finishing a free course.
 *
 * The system only ever issues this for a completion it has observed: the
 * enrolment must be at 100% before a certificate exists, and issuing is
 * idempotent so a repeated attempt cannot mint a second one.
 */
class IssueCourseCertificate
{
    public function execute(CourseEnrollment $enrollment): ?CourseCertificate
    {
        $enrollment->refresh();

        if (! $enrollment->isComplete()) {
            return null;
        }

        if ($existing = $enrollment->certificate()) {
            return $existing;
        }

        $user = $enrollment->user;

        if ($user === null) {
            throw new RuntimeException('Cannot issue a certificate without a recipient.');
        }

        return CourseCertificate::query()->create([
            'course_id' => $enrollment->course_id,
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'issued_at' => now(),
        ]);
    }
}
