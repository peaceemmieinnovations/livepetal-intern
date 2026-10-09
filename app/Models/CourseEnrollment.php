<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A member's progress through a course. Progress is derived from completed
 * modules so it can always be recomputed from the underlying rows.
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property int $progress_percent
 * @property Carbon|null $completed_at
 */
#[Fillable([
    'course_id', 'user_id', 'enrolled_at', 'started_at', 'completed_at',
    'last_accessed_at', 'progress_percent',
])]
class CourseEnrollment extends Model
{
    /** @use HasFactory<CourseEnrollmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'progress_percent' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CourseModuleCompletion::class);
    }

    /**
     * The certificate earned for this course, if the member finished it.
     */
    public function certificate(): ?CourseCertificate
    {
        return CourseCertificate::query()
            ->where('course_id', $this->course_id)
            ->where('user_id', $this->user_id)
            ->first();
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Recompute progress from completed modules. Called after any module is
     * completed so the stored percentage can never drift from reality.
     */
    public function recalculateProgress(): void
    {
        $total = $this->course->publishedModules()->count();

        if ($total === 0) {
            $this->forceFill(['progress_percent' => 0])->save();

            return;
        }

        $done = $this->completions()
            ->whereHas('module', fn ($query) => $query->where('is_published', true))
            ->count();

        $percent = (int) round(min($done, $total) / $total * 100);

        $this->forceFill([
            'progress_percent' => $percent,
            'started_at' => $this->started_at ?? now(),
            'last_accessed_at' => now(),
            'completed_at' => $percent === 100 ? ($this->completed_at ?? now()) : null,
        ])->save();
    }
}
