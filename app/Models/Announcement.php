<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $audience
 * @property bool $is_pinned
 * @property string $status
 * @property Carbon|null $published_at
 */
#[Fillable([
    'title', 'body', 'audience', 'cohort_id', 'is_pinned', 'status', 'created_by', 'published_at',
])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_STUDENTS = 'students';

    public const AUDIENCE_SUPERVISORS = 'supervisors';

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    /**
     * Announcements a given user should see, honouring audience and cohort.
     *
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    #[Scope]
    public function forUser(Builder $query, User $user): Builder
    {
        return $query->published()->where(function (Builder $q) use ($user): void {
            $q->where('audience', self::AUDIENCE_ALL);

            if ($user->isStudent()) {
                $q->orWhere('audience', self::AUDIENCE_STUDENTS);
            }

            if ($user->isSupervisor()) {
                $q->orWhere('audience', self::AUDIENCE_SUPERVISORS);
            }

            $cohortId = $user->studentProfile?->cohort_id;

            if ($cohortId !== null) {
                $q->orWhere('cohort_id', $cohortId);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function audienceOptions(): array
    {
        return [
            self::AUDIENCE_ALL => 'Everyone',
            self::AUDIENCE_STUDENTS => 'Trainees',
            self::AUDIENCE_SUPERVISORS => 'Supervisors',
        ];
    }
}
