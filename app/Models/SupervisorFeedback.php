<?php

namespace App\Models;

use App\Enums\FeedbackCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A supervisor's written comment on a trainee. Ratings accompany the comment,
 * they never stand in for it.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int $supervisor_id
 * @property FeedbackCategory $category
 * @property int|null $rating
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property bool $is_shared_with_student
 */
#[Fillable([
    'student_profile_id', 'supervisor_id', 'category', 'comments', 'rating',
    'subject_type', 'subject_id', 'recommendations', 'is_shared_with_student',
])]
class SupervisorFeedback extends Model
{
    /** @use HasFactory<SupervisorFeedbackFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'category' => FeedbackCategory::class,
            'rating' => 'integer',
            'is_shared_with_student' => 'boolean',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity, weekly summary or project this feedback is attached to.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
