<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An application tracked against a Livepetal job posting. Visitors may apply
 * without an account; when they are signed in the application is linked to
 * their user record. CV uploads are private Documents.
 *
 * @property int $id
 * @property int $job_posting_id
 * @property int|null $user_id
 * @property int|null $document_id
 * @property string $applicant_name
 * @property string $applicant_email
 * @property ApplicationStatus $status
 */
#[Fillable([
    'job_posting_id', 'user_id', 'document_id', 'applicant_name', 'applicant_email',
    'applicant_phone', 'portfolio_url', 'linkedin_url', 'cover_note', 'status',
    'internal_notes', 'reviewed_by', 'reviewed_at',
])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<JobApplication>  $query
     * @return Builder<JobApplication>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ApplicationStatus::Rejected->value,
            ApplicationStatus::Withdrawn->value,
        ]);
    }

    /**
     * @param  Builder<JobApplication>  $query
     * @return Builder<JobApplication>
     */
    #[Scope]
    public function submittedBy(Builder $query, string $email): Builder
    {
        return $query->where('applicant_email', $email);
    }
}
