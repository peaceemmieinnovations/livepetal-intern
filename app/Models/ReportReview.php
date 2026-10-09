<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $report_version_id
 * @property int|null $reviewer_id
 * @property string $decision
 * @property string|null $comments
 */
#[Fillable(['report_version_id', 'reviewer_id', 'decision', 'comments'])]
class ReportReview extends Model
{
    /** @use HasFactory<ReportReviewFactory> */
    use HasFactory;

    public const DECISION_APPROVED = 'approved';

    public const DECISION_REVISIONS = 'revisions_requested';

    public const DECISION_COMMENTED = 'commented';

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportVersion::class, 'report_version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decisionLabel(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVED => 'Approved',
            self::DECISION_REVISIONS => 'Revisions requested',
            default => 'Commented',
        };
    }
}
