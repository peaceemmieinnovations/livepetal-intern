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
 * A threaded reply on a community post.
 *
 * @property int $id
 * @property int $community_post_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $body
 * @property CommentStatus $status
 */
#[Fillable([
    'community_post_id', 'user_id', 'parent_id', 'body', 'status',
    'moderated_by', 'moderated_at',
])]
class CommunityComment extends Model
{
    /** @use HasFactory<CommunityCommentFactory> */
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
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
     * @param  Builder<CommunityComment>  $query
     * @return Builder<CommunityComment>
     */
    #[Scope]
    public function visible(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Published->value);
    }
}
