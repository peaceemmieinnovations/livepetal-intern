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
 * The SIWES placement of one trainee: which cohort they belong to, which
 * supervisor oversees them and the official training window.
 *
 * @property int $id
 * @property int $student_id
 * @property int $cohort_id
 * @property int|null $supervisor_id
 * @property string|null $training_area
 * @property string|null $unit
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string $status
 * @property string|null $notes
 * @property-read User $student
 * @property-read User|null $supervisor
 * @property-read Cohort $cohort
 */
#[Fillable([
    'student_id', 'cohort_id', 'supervisor_id',
    'training_area', 'unit', 'starts_on', 'ends_on', 'status', 'notes', 'approved_at',
])]
class Placement extends Model
{
    /** @use HasFactory<PlacementFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id', 'user_id');
    }

    /**
     * @param  Builder<Placement>  $query
     * @return Builder<Placement>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function totalDays(): int
    {
        return $this->starts_on->diffInDays($this->ends_on) + 1;
    }

    public function daysElapsed(): int
    {
        return (int) min(max($this->starts_on->diffInDays(today()), 0), $this->totalDays());
    }

    public function daysRemaining(): int
    {
        return (int) max($this->ends_on->diffInDays(today()), 0);
    }

    /**
     * Percentage of the training window that has passed, 0-100.
     */
    public function progressPercentage(): int
    {
        $total = $this->totalDays();

        return $total > 0 ? (int) round($this->daysElapsed() / $total * 100) : 0;
    }

    public function isActive(): bool
    {
        return now()->between($this->starts_on->startOfDay(), $this->ends_on->endOfDay());
    }
}
