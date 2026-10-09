<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A certificate a trainee has earned. The scan itself is a Document; this row
 * holds the descriptive metadata and verification state.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int|null $document_id
 * @property Visibility $visibility
 * @property ReviewStatus $review_status
 */
#[Fillable([
    'student_profile_id', 'title', 'issuer', 'issued_on', 'credential_url',
    'document_id', 'visibility', 'review_status', 'verified_by', 'verified_at',
])]
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'visibility' => Visibility::class,
            'review_status' => ReviewStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @param  Builder<Certificate>  $query
     * @return Builder<Certificate>
     */
    #[Scope]
    public function verified(Builder $query): Builder
    {
        return $query->where('review_status', ReviewStatus::Verified->value);
    }

    /**
     * @param  Builder<Certificate>  $query
     * @return Builder<Certificate>
     */
    #[Scope]
    public function publicFacing(Builder $query): Builder
    {
        return $query
            ->whereIn('visibility', Visibility::publicLevels())
            ->where('review_status', ReviewStatus::Verified->value);
    }
}
