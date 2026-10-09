<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\ContentStatus;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ContentStatus $status
 * @property bool $is_featured
 * @property int $views_count
 * @property Carbon|null $published_at
 */
#[Fillable([
    'blog_category_id', 'author_id', 'title', 'slug', 'excerpt', 'body',
    'cover_image_path', 'meta_description', 'status', 'is_featured',
    'reading_minutes', 'published_at',
])]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'reading_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            if (blank($post->slug)) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }

            if (blank($post->reading_minutes)) {
                $post->reading_minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
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
        return $this->belongsTo(User::class, 'author_id');
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->where('status', CommentStatus::Published->value);
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    /**
     * @param  Builder<BlogPost>  $query
     * @return Builder<BlogPost>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Ranking feed weighted towards fresh, discussed articles rather than raw
     * view counts, so a single viral visit cannot dominate the page. Computed
     * in PHP so the same ordering works on MySQL and SQLite.
     *
     * @return Collection<int, self>
     */
    public static function trending(int $limit = 6): Collection
    {
        return static::query()
            ->published()
            ->with(['author', 'primaryCategory'])
            ->withCount(['comments' => fn ($q) => $q->visible()])
            ->orderByDesc('published_at')
            ->limit(50)
            ->get()
            ->sortByDesc(self::trendScore(...))
            ->take($limit)
            ->values();
    }

    public static function trendScore(self $post): float
    {
        $ageInHours = max($post->published_at?->diffInHours(now()) ?? 720, 1);

        return (100 / $ageInHours)
            + ($post->views_count ?? 0)
            + (($post->comments_count ?? 0) * 25);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
