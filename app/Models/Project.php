<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_profile_id
 * @property string $name
 * @property string $slug
 * @property array<int, string>|null $technologies
 * @property Carbon|null $starts_on
 * @property Carbon|null $ends_on
 * @property ProjectStatus $status
 * @property Visibility $visibility
 * @property ReviewStatus $review_status
 */
#[Fillable([
    'student_profile_id', 'name', 'slug', 'description', 'problem', 'solution', 'role',
    'technologies', 'documentation', 'starts_on', 'ends_on', 'status', 'repository_url',
    'live_url', 'visibility', 'review_status', 'verified_by', 'verified_at', 'verifier_note',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'documentation' => 'array',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => ProjectStatus::class,
            'visibility' => Visibility::class,
            'review_status' => ReviewStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function reflections(): HasMany
    {
        return $this->hasMany(Reflection::class);
    }

    public function workReferences(): HasMany
    {
        return $this->hasMany(WorkReference::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('sort_order');
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    #[Scope]
    public function verified(Builder $query): Builder
    {
        return $query->where('review_status', ReviewStatus::Verified->value);
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    #[Scope]
    public function publicFacing(Builder $query): Builder
    {
        return $query
            ->whereIn('visibility', Visibility::publicLevels())
            ->where('review_status', ReviewStatus::Verified->value);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->review_status === ReviewStatus::Verified
            && in_array($this->visibility->value, Visibility::publicLevels(), true);
    }
}
