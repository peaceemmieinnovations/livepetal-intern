<?php

namespace App\Enums;

/**
 * Who may see a trainee-authored record.
 */
enum Visibility: string
{
    case Private = 'private';
    case SupervisorOnly = 'supervisor_only';
    case Community = 'community';
    case PublicPortfolio = 'public_portfolio';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Only me',
            self::SupervisorOnly => 'Me and my supervisor',
            self::Community => 'Livepetal Intern community',
            self::PublicPortfolio => 'Public portfolio',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Private => 'Nobody else can read this.',
            self::SupervisorOnly => 'Visible to you and your assigned supervisor.',
            self::Community => 'Visible to signed-in trainees and staff inside Livepetal Intern.',
            self::PublicPortfolio => 'Visible to anyone on your public portfolio page.',
        };
    }

    /**
     * Visibility levels a record must reach before it may appear publicly.
     *
     * @return array<int, string>
     */
    public static function publicLevels(): array
    {
        return [self::Community->value, self::PublicPortfolio->value];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $visibility): array => [$visibility->value => $visibility->label()])
            ->all();
    }
}
