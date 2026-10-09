<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A week-in-review. Prefilled from the trainee's own activities but never
 * written automatically — the student always reviews and submits it.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property Carbon $week_starts_on
 * @property Carbon $week_ends_on
 * @property array<int, int>|null $activity_ids
 * @property array<int, int>|null $skill_ids
 * @property array<int, int>|null $project_ids
 * @property ActivityStatus $status
 */
#[Fillable([
    'student_profile_id', 'week_starts_on', 'week_ends_on', 'summary', 'major_learning',
    'challenges', 'achievements', 'next_week_goals', 'activity_ids', 'skill_ids',
    'project_ids', 'status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'reviewer_note',
])]
class WeeklySummary extends Model
{
    /** @use HasFactory<WeeklySummaryFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'week_starts_on' => 'date',
            'week_ends_on' => 'date',
            'activity_ids' => 'array',
            'skill_ids' => 'array',
            'project_ids' => 'array',
            'status' => ActivityStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<WeeklySummary>  $query
     * @return Builder<WeeklySummary>
     */
    #[Scope]
    public function awaitingReview(Builder $query): Builder
    {
        return $query->where('status', ActivityStatus::Submitted->value);
    }

    /**
     * @param  Builder<WeeklySummary>  $query
     * @return Builder<WeeklySummary>
     */
    #[Scope]
    public function reviewed(Builder $query): Builder
    {
        return $query->where('status', ActivityStatus::Published->value);
    }

    public function isComplete(): bool
    {
        return filled($this->summary) && filled($this->major_learning);
    }
}
