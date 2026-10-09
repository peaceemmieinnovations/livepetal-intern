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
 * Documentation of work a trainee was assigned through Livepetal's existing
 * internal shift/work system. This records the SIWES paper trail only — it
 * never replaces or interfaces with that internal system.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int|null $project_id
 * @property string $work_title
 * @property Carbon $worked_on
 * @property string|null $short_description
 * @property string|null $outcome
 * @property string|null $evidence_link
 * @property Visibility $visibility
 */
#[Fillable([
    'student_profile_id', 'project_id', 'work_title', 'worked_on',
    'short_description', 'outcome', 'evidence_link', 'visibility',
])]
class WorkReference extends Model
{
    /** @use HasFactory<WorkReferenceFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'worked_on' => 'date',
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
}
