<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * An approved contribution to the shared knowledge base. A resource flagged as
 * containing confidential company material can never be published.
 *
 * @property int $id
 * @property int|null $student_profile_id
 * @property string $title
 * @property string $slug
 * @property string $type
 * @property ContentStatus $status
 * @property bool $contains_company_confidential
 * @property Visibility $visibility
 */
#[Fillable([
    'student_profile_id', 'skill_id', 'reviewed_by', 'title', 'slug', 'summary', 'body',
    'type', 'tags', 'status', 'visibility', 'contains_company_confidential',
    'reviewer_note', 'published_at',
])]
class KnowledgeResource extends Model
{
    /** @use HasFactory<KnowledgeResourceFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'status' => ContentStatus::class,
            'visibility' => Visibility::class,
            'contains_company_confidential' => 'boolean',
            'published_at' => 'datetime',
            'helpful_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $resource): void {
            if (blank($resource->slug)) {
                $resource->slug = static::uniqueSlug($resource->title, $resource->id);
            }

            // Confidential material is never publishable, whatever the reviewer
            // selects. The restriction lives in the model as well as the policy.
            if ($resource->contains_company_confidential) {
                $resource->status = ContentStatus::Archived;
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'resource';
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
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<KnowledgeResource>  $query
     * @return Builder<KnowledgeResource>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->where('contains_company_confidential', false)
            ->whereNotNull('published_at');
    }

    /**
     * @param  Builder<KnowledgeResource>  $query
     * @return Builder<KnowledgeResource>
     */
    #[Scope]
    public function awaitingReview(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Draft->value);
    }

    public function canBePublished(): bool
    {
        return ! $this->contains_company_confidential;
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'tutorial' => 'Tutorial',
            'setup_guide' => 'Setup guide',
            'lesson_learned' => 'Lesson learned',
            'troubleshooting' => 'Troubleshooting note',
            'project_notes' => 'Project notes',
            'best_practice' => 'Best practice',
            'resource_link' => 'Useful resource',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? ucfirst((string) $this->type);
    }
}
