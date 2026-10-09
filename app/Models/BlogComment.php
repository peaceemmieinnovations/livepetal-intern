<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reader comment on a blog article. Signed-in users are linked; guests may
 * comment with a name and email which is never displayed publicly.
 *
 * @property int $id
 * @property int $blog_post_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string|null $guest_name
 * @property string|null $guest_email
 * @property string $body
 * @property CommentStatus $status
 */
#[Fillable([
    'blog_post_id', 'user_id', 'parent_id', 'guest_name', 'guest_email',
    'body', 'status', 'moderated_by', 'moderated_at',
])]
class BlogComment extends Model
{
    /** @use HasFactory<BlogCommentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'moderated_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * @param  Builder<BlogComment>  $query
     * @return Builder<BlogComment>
     */
    #[Scope]
    public function visible(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Published->value);
    }

    /**
     * The name shown next to the comment. Guest email is never exposed.
     */
    public function authorName(): string
    {
        return $this->user?->name
            ?? $this->guest_name
            ?? 'Anonymous';
    }

    public function authorInitials(): string
    {
        return $this->user?->initials()
            ?? mb_substr((string) $this->authorName(), 0, 2);
    }
}
