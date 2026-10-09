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
 * @property int $id
 * @property int $student_profile_id
 * @property Carbon $month_starts_on
 * @property Carbon $month_ends_on
 * @property string|null $supervisor_feedback
 * @property ActivityStatus $status
 */
#[Fillable([
    'student_profile_id', 'month_starts_on', 'month_ends_on', 'summary', 'major_learning',
    'challenges', 'achievements', 'student_reflection', 'supervisor_feedback',
    'activity_ids', 'skill_ids', 'project_ids', 'status', 'submitted_at',
])]
class MonthlySummary extends Model
{
    /** @use HasFactory<MonthlySummaryFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'month_starts_on' => 'date',
            'month_ends_on' => 'date',
            'activity_ids' => 'array',
            'skill_ids' => 'array',
            'project_ids' => 'array',
            'status' => ActivityStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    /**
     * @param  Builder<MonthlySummary>  $query
     * @return Builder<MonthlySummary>
     */
    #[Scope]
    public function reviewed(Builder $query): Builder
    {
        return $query->where('status', ActivityStatus::Published->value);
    }
}
