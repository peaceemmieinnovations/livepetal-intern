<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A role advertised by Livepetal Systems Limited. Applications are tracked
 * internally; CV uploads are private documents.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $status
 * @property Carbon|null $deadline
 * @property Carbon|null $published_at
 */
#[Fillable([
    'created_by', 'title', 'slug', 'team', 'employment_type', 'location', 'is_remote',
    'summary', 'description', 'responsibilities', 'requirements', 'benefits',
    'skill_ids', 'deadline', 'is_accepting_applications', 'applications_open_at',
    'applications_close_at', 'status', 'is_featured', 'published_at',
])]
class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_remote' => 'boolean',
            'is_featured' => 'boolean',
            'is_accepting_applications' => 'boolean',
            'skill_ids' => 'array',
            'deadline' => 'date',
            'applications_open_at' => 'datetime',
            'applications_close_at' => 'datetime',
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $posting): void {
            if (blank($posting->slug)) {
                $posting->slug = static::uniqueSlug($posting->title, $posting->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'role';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function skills()
    {
        return Skill::query()->whereIn('id', $this->skill_ids ?? []);
    }

    /**
     * @param  Builder<JobPosting>  $query
     * @return Builder<JobPosting>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at');
    }

    /**
     * @param  Builder<JobPosting>  $query
     * @return Builder<JobPosting>
     */
    #[Scope]
    public function open(Builder $query): Builder
    {
        return $query->published()
            ->where(fn (Builder $q): Builder => $q->whereNull('deadline')->orWhere('deadline', '>=', today()));
    }

    /**
     * Listings where the Apply button is currently shown.
     *
     * @param  Builder<JobPosting>  $query
     * @return Builder<JobPosting>
     */
    #[Scope]
    public function acceptingApplications(Builder $query): Builder
    {
        return $query->open()->where('is_accepting_applications', true);
    }

    public function isOpen(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->deadline === null || $this->deadline->greaterThanOrEqualTo(today()));
    }

    /**
     * Whether the Apply button is shown. The listing and its deadline stay public
     * either way; an administrator switches submissions on and off, and the
     * window narrows that further.
     */
    public function isAcceptingApplications(): bool
    {
        if (! $this->isOpen() || ! $this->is_accepting_applications) {
            return false;
        }

        $now = now();

        if ($this->applications_open_at !== null && $now->lt($this->applications_open_at)) {
            return false;
        }

        if ($this->applications_close_at !== null && $now->gt($this->applications_close_at)) {
            return false;
        }

        return true;
    }

    /**
     * Why applications are closed, so the page can explain itself instead of
     * just hiding the button.
     */
    public function applicationsClosedReason(): ?string
    {
        if ($this->isAcceptingApplications()) {
            return null;
        }

        if (! $this->isOpen()) {
            return $this->deadline !== null && $this->deadline->isPast()
                ? 'The application deadline for this opportunity has passed.'
                : 'This opportunity is not currently open.';
        }

        if (! $this->is_accepting_applications) {
            return 'Applications are temporarily paused. Check back shortly.';
        }

        if ($this->applications_open_at !== null && now()->lt($this->applications_open_at)) {
            return "Applications open on {$this->applications_open_at->format('j F Y')}.";
        }

        return "Applications closed on {$this->applications_close_at?->format('j F Y')}.";
    }

    public function hasClosed(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }

    public function daysUntilDeadline(): ?int
    {
        return $this->deadline?->diffInDays(today());
    }

    /**
     * @return array<string, string>
     */
    public static function employmentTypeOptions(): array
    {
        return [
            'full_time' => 'Full time',
            'part_time' => 'Part time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'remote' => 'Remote',
        ];
    }

    public function employmentTypeLabel(): string
    {
        return self::employmentTypeOptions()[$this->employment_type] ?? ucfirst((string) $this->employment_type);
    }

    public function locationLabel(): string
    {
        if ($this->is_remote) {
            return 'Remote';
        }

        return $this->location ?? 'Livepetal Systems Limited';
    }
}
