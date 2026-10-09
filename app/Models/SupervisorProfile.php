<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $job_title
 * @property string|null $team
 * @property string|null $phone
 * @property string|null $office_location
 * @property string|null $bio
 * @property array<int, string>|null $specialisms
 * @property bool $accepting_reviews
 * @property-read User $user
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id', 'job_title', 'team', 'phone', 'office_location',
    'bio', 'specialisms', 'accepting_reviews',
])]
class SupervisorProfile extends Model
{
    /** @use HasFactory<SupervisorProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'specialisms' => 'array',
            'accepting_reviews' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class, 'supervisor_id', 'user_id');
    }

    public function feedbackGiven(): HasMany
    {
        return $this->hasMany(SupervisorFeedback::class, 'supervisor_id', 'user_id');
    }
}
