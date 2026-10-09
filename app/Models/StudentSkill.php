<?php

namespace App\Models;

use App\Enums\SkillLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A trainee's self-declared proficiency in a skill, optionally linked to
 * evidence and verified by a supervisor.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int $skill_id
 * @property SkillLevel $level
 * @property Carbon|null $first_practiced_on
 * @property Carbon|null $last_practiced_on
 * @property string|null $evidence
 * @property bool $is_verified
 * @property int|null $project_id
 * @property-read Skill $skill
 * @property-read Project|null $project
 */
#[Fillable([
    'student_profile_id', 'skill_id', 'level', 'first_practiced_on', 'last_practiced_on',
    'evidence', 'is_verified', 'verified_by', 'verified_at', 'project_id',
])]
class StudentSkill extends Model
{
    /** @use HasFactory<StudentSkillFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'level' => SkillLevel::class,
            'first_practiced_on' => 'date',
            'last_practiced_on' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
