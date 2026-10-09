<?php

namespace App\Enums;

/**
 * Moderation state for user-authored public comments and community posts.
 */
enum CommentStatus: string
{
    case Published = 'published';
    case Pending = 'pending';
    case Hidden = 'hidden';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Pending => 'Awaiting moderation',
            self::Hidden => 'Hidden',
            self::Spam => 'Marked as spam',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Pending => 'warning',
            self::Hidden, self::Spam => 'danger',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Published;
    }
}
