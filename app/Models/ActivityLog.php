<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * The core daily journal entry. Everything a trainee records flows through here
 * and is later used as traceable source material by the Report Manager.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int|null $project_id
 * @property Carbon $activity_date
 * @property string $title
 * @property string|null $description
 * @property string|null $learned
 * @property string|null $challenge
 * @property string|null $resolution
 * @property string|null $reflection
 * @property array<int, string>|null $technologies
 * @property string|null $external_links
 * @property Visibility $visibility
 * @property ActivityStatus $status
 * @property int|null $reviewed_by
 * @property string|null $reviewer_note
 * @property Carbon|null $published_at
 */
#[Fillable([
    'student_profile_id', 'project_id', 'activity_date', 'title', 'description',
    'learned', 'challenge', 'resolution', 'reflection', 'technologies',
    'external_links', 'visibility', 'status', 'reviewed_by', 'reviewed_at',
    'reviewer_note', 'submitted_at', 'published_at',
])]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'technologies' => 'array',
            'visibility' => Visibility::class,
            'status' => ActivityStatus::class,
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('sort_order');
    }

    /**
     * @param  Builder<ActivityLog>  $query
     * @return Builder<ActivityLog>
     */
    #[Scope]
    public function reviewed(Builder $query): Builder
    {
        return $query->where('status', ActivityStatus::Published->value);
    }

    /**
     * @param  Builder<ActivityLog>  $query
     * @return Builder<ActivityLog>
     */
    #[Scope]
    public function awaitingReview(Builder $query): Builder
    {
        return $query->where('status', ActivityStatus::Submitted->value);
    }

    /**
     * @param  Builder<ActivityLog>  $query
     * @return Builder<ActivityLog>
     */
    #[Scope]
    public function publicFacing(Builder $query): Builder
    {
        return $query->whereIn('visibility', Visibility::publicLevels());
    }

    /**
     * @param  Builder<ActivityLog>  $query
     * @return Builder<ActivityLog>
     */
    #[Scope]
    public function betweenDates(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('activity_date', [$start, $end]);
    }

    public function isReportable(): bool
    {
        return $this->status->isReportable();
    }

    public function isDraft(): bool
    {
        return $this->status === ActivityStatus::Draft;
    }

    public function canBeEdited(): bool
    {
        return $this->status !== ActivityStatus::Published;
    }
}
