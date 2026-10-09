<?php

namespace App\Enums;

/**
 * Review lifecycle for a trainee activity entry.
 */
enum ActivityStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Published = 'published';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Awaiting review',
            self::Published => 'Reviewed',
            self::Returned => 'Returned',
        };
    }

    /**
     * Only reviewed entries may be used as report source material.
     */
    public function isReportable(): bool
    {
        return $this === self::Published;
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Submitted => 'warning',
            self::Published => 'success',
            self::Returned => 'danger',
        };
    }
}
