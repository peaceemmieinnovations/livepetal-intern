<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One trainee's SIWES report. Versions hold the actual content; this row tracks
 * the workflow state and which version is currently authoritative.
 *
 * @property int $id
 * @property int $student_profile_id
 * @property int $report_template_id
 * @property ReportStatus $status
 * @property int $current_version
 * @property int|null $current_version_id
 * @property-read ReportVersion|null $currentVersion
 */
#[Fillable([
    'student_profile_id', 'cohort_id', 'report_template_id', 'title', 'status',
    'current_version', 'current_version_id', 'submitted_at', 'approved_at',
    'approved_by', 'finalized_at', 'finalized_by',
])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'current_version' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ReportVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ReportVersion::class, 'current_version_id');
    }

    public function latestVersion(): ?ReportVersion
    {
        return $this->versions()->first();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    #[Scope]
    public function needingAction(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ReportStatus::Submitted->value,
            ReportStatus::UnderReview->value,
            ReportStatus::RevisionRequested->value,
        ]);
    }

    public function isOfficial(): bool
    {
        return $this->status->isOfficial();
    }
}
