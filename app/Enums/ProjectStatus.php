<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Planned => 'neutral',
            self::InProgress => 'info',
            self::Completed => 'success',
            self::Archived => 'neutral',
        };
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
