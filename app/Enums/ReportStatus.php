<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted for review',
            self::UnderReview => 'Under review',
            self::RevisionRequested => 'Revision requested',
            self::Approved => 'Approved',
            self::Final => 'Final',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Submitted => 'info',
            self::UnderReview => 'warning',
            self::RevisionRequested => 'danger',
            self::Approved => 'success',
            self::Final => 'success',
        };
    }

    /**
     * Only an approved or finalised report may be exported as the official
     * SIWES document. Drafts can still be exported as a working copy.
     */
    public function isOfficial(): bool
    {
        return in_array($this, [self::Approved, self::Final], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
