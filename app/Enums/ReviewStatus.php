<?php

namespace App\Enums;

/**
 * Verification applied by a supervisor or administrator to trainee records.
 */
enum ReviewStatus: string
{
    case Unreviewed = 'unreviewed';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unreviewed => 'Unreviewed',
            self::Pending => 'Needs review',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Unreviewed => 'neutral',
            self::Pending => 'warning',
            self::Verified => 'success',
            self::Rejected => 'danger',
        };
    }
}
