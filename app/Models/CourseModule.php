<?php

namespace App\Models;

use App\Enums\ModuleContentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One step of a free course. A module is complete once the learner has opened
 * or marked it done; progress is tracked per enrolment so it survives a new
 * enrolment.
 *
 * @property int $id
 * @property int $course_id
 * @property ModuleContentType $content_type
 * @property int $sort_order
 */
#[Fillable([
    'course_id', 'title', 'content', 'content_type', 'external_url',
    'duration_minutes', 'sort_order', 'is_published',
])]
class CourseModule extends Model
{
    /** @use HasFactory<CourseModuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'content_type' => ModuleContentType::class,
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CourseModuleCompletion::class);
    }

    public function isCompletedBy(?CourseEnrollment $enrollment): bool
    {
        if ($enrollment === null) {
            return false;
        }

        return $this->completions()
            ->where('course_enrollment_id', $enrollment->id)
            ->exists();
    }

    public function nextFor(CourseEnrollment $enrollment): ?CourseModule
    {
        return $enrollment->course
            ->publishedModules()
            ->get()
            ->first(fn (CourseModule $module): bool => ! $module->isCompletedBy($enrollment));
    }
}
