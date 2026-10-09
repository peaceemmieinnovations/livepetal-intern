<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of significant actions. Never updated, never cascaded
 * away by deleting the actor.
 *
 * @property int $id
 * @property int|null $actor_id
 * @property string $action
 * @property string $description
 * @property string|null $target_type
 * @property int|null $target_id
 * @property array<string, mixed>|null $metadata
 */
#[Fillable([
    'actor_id', 'action', 'description', 'target_type', 'target_id',
    'metadata', 'ip_address', 'user_agent',
])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @param  Builder<AuditLog>  $query
     * @return Builder<AuditLog>
     */
    #[Scope]
    public function forAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * @param  Builder<AuditLog>  $query
     * @return Builder<AuditLog>
     */
    #[Scope]
    public function forTarget(Builder $query, Model $target): Builder
    {
        return $query->where('target_type', $target->getMorphClass())
            ->where('target_id', $target->getKey());
    }
}
