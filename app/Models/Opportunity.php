<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\OpportunityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A curated opportunity: internships, jobs, scholarships and training
 * programmes shared with trainees. Publication never implies a guarantee of
 * placement.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property OpportunityType $type
 * @property Carbon|null $deadline
 * @property ContentStatus $status
 * @property bool $is_remote
 */
#[Fillable([
    'created_by', 'title', 'slug', 'organization', 'description', 'type', 'location',
    'is_remote', 'deadline', 'external_url', 'status', 'published_at',
])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => OpportunityType::class,
            'is_remote' => 'boolean',
            'deadline' => 'date',
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $opportunity): void {
            if (blank($opportunity->slug)) {
                $opportunity->slug = static::uniqueSlug($opportunity->title, $opportunity->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'opportunity';
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

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(SavedOpportunity::class);
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return Builder<Opportunity>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return Builder<Opportunity>
     */
    #[Scope]
    public function open(Builder $query): Builder
    {
        return $query->published()
            ->where(fn (Builder $q): Builder => $q->whereNull('deadline')->orWhere('deadline', '>=', today()));
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return Builder<Opportunity>
     */
    #[Scope]
    public function closingSoon(Builder $query, int $days = 14): Builder
    {
        return $query->open()->whereNotNull('deadline')
            ->whereBetween('deadline', [today(), today()->addDays($days)]);
    }

    public function isOpen(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->deadline === null || $this->deadline->isFuture() || $this->deadline->isToday());
    }

    public function daysUntilDeadline(): ?int
    {
        return $this->deadline?->diffInDays(today());
    }
}
