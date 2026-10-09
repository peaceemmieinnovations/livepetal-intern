<?php

namespace App\Enums;

/**
 * Pipeline state for an internal job application tracked by Livepetal.
 */
enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::Shortlisted => 'Shortlisted',
            self::Interview => 'Interview',
            self::Offer => 'Offer',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Submitted => 'neutral',
            self::UnderReview => 'info',
            self::Shortlisted, self::Interview => 'warning',
            self::Offer => 'success',
            self::Rejected, self::Withdrawn => 'danger',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Rejected, self::Withdrawn, self::Offer], true);
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
