<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\CommunityPostType;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A post in the Livepetal Intern community feed. Trainees share what they are
 * building and help each other; likes and threaded replies are real records.
 *
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property array<int, string>|null $tags
 * @property Visibility $visibility
 * @property CommentStatus $status
 * @property int $likes_count
 * @property int $comments_count
 * @property bool $is_pinned
 * @property CommunityPostType $post_type
 * @property bool $is_solved
 */
#[Fillable([
    'user_id', 'project_id', 'post_type', 'body', 'tags', 'visibility', 'status',
    'is_pinned', 'pinned_by', 'pinned_at', 'is_solved', 'solved_by', 'solved_at',
])]
class CommunityPost extends Model
{
    /** @use HasFactory<CommunityPostFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'visibility' => Visibility::class,
            'status' => CommentStatus::class,
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'post_type' => CommunityPostType::class,
            'is_solved' => 'boolean',
            'solved_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityPostLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class);
    }

    public function visibleComments(): HasMany
    {
        return $this->comments()->where('status', CommentStatus::Published->value);
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    /**
     * The member who marked this problem resolved.
     */
    public function solvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solved_by');
    }

    public function likedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->relationLoaded('likes')
            ? $this->likes->contains('user_id', $user->id)
            : $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Published->value);
    }

    /**
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    #[Scope]
    public function ranked(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('likes_count')->orderByDesc('created_at');
    }

    /**
     * Problems and questions, unsolved first then oldest: the order you would
     * work through a help board in.
     *
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    #[Scope]
    public function problems(Builder $query): Builder
    {
        return $query->published()
            ->whereIn('post_type', CommunityPostType::answerable())
            ->orderBy('is_solved')
            ->orderBy('created_at');
    }

    /**
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    #[Scope]
    public function unsolved(Builder $query): Builder
    {
        return $query->where('is_solved', false);
    }

    public function isAnswerable(): bool
    {
        return $this->post_type->isAnswerable();
    }

    public function authorName(): string
    {
        return $this->author?->name ?? 'Former trainee';
    }

    public function authorRole(): string
    {
        return $this->author?->role?->label() ?? 'Trainee';
    }
}
