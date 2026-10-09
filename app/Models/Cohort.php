<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A SIWES cohort. Cohorts are the unit of continuity between years, so they
 * are never hard-deleted once trainees have been placed in them.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $academic_year
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string $status
 * @property string|null $notes
 */
#[Fillable(['name', 'slug', 'academic_year', 'starts_on', 'ends_on', 'status', 'notes', 'created_by'])]
class Cohort extends Model
{
    /** @use HasFactory<CohortFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * @param  Builder<Cohort>  $query
     * @return Builder<Cohort>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isCurrent(): bool
    {
        return now()->between($this->starts_on->startOfDay(), $this->ends_on->endOfDay());
    }

    public function totalDays(): int
    {
        return $this->starts_on->diffInDays($this->ends_on) + 1;
    }
}
