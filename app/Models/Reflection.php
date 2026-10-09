<?php

namespace App\Models;

use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A private learning journal entry. Reflections are never published publicly and
 * never leave the platform.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int|null $project_id
 * @property int|null $skill_id
 * @property Carbon $reflection_date
 * @property string $title
 * @property Visibility $visibility
 */
#[Fillable([
    'student_profile_id', 'project_id', 'skill_id', 'reflection_date', 'title',
    'learned', 'challenge', 'resolution', 'improvements', 'body', 'visibility',
])]
class Reflection extends Model
{
    /** @use HasFactory<ReflectionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'reflection_date' => 'date',
            'visibility' => Visibility::class,
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
