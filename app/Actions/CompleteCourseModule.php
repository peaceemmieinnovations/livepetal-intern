<?php

namespace App\Actions;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;

/**
 * Marks a module finished for one enrolment, recomputes progress and issues the
 * certificate when the course reaches 100%.
 *
 * Completion is idempotent: repeating it must not inflate progress or issue a
 * second certificate.
 */
class CompleteCourseModule
{
    public function execute(CourseModule $module, CourseEnrollment $enrollment): CourseEnrollment
    {
        $enrollment->course->modules()
            ->whereKey($module->getKey())
            ->where('is_published', true)
            ->firstOrFail();

        $enrollment->completions()
            ->firstOrCreate(
                ['course_module_id' => $module->getKey()],
                ['user_id' => $enrollment->user_id, 'completed_at' => now()],
            );

        $enrollment->recalculateProgress();
        $enrollment->refresh();

        if ($enrollment->isComplete()) {
            app(IssueCourseCertificate::class)->execute($enrollment);
        }

        return $enrollment->refresh();
    }
}
