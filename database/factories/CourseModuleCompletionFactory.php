<?php

namespace Database\Factories;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\CourseModuleCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModuleCompletion>
 */
class CourseModuleCompletionFactory extends Factory
{
    public function definition(): array
    {
        $enrollment = CourseEnrollment::factory()->create();
        $module = CourseModule::factory()->create(['course_id' => $enrollment->course_id]);

        return [
            'course_module_id' => $module->id,
            'course_enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'completed_at' => now(),
        ];
    }
}
