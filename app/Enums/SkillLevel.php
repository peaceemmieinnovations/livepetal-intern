<?php

namespace App\Enums;

/**
 * Self-declared skill level. A level is never raised automatically from the
 * number of activity records — a supervisor or the student must set it.
 */
enum SkillLevel: string
{
    case Beginner = 'beginner';
    case Developing = 'developing';
    case Competent = 'competent';
    case Advanced = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Developing => 'Developing',
            self::Competent => 'Competent',
            self::Advanced => 'Advanced',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Beginner => 'New to this. Following guidance and tutorials.',
            self::Developing => 'Can complete guided tasks with occasional support.',
            self::Competent => 'Works independently on real tasks in this area.',
            self::Advanced => 'Can design, review and advise others in this area.',
        };
    }

    /**
     * Weight used only for sorting/display, never for automatic promotion.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Beginner => 1,
            self::Developing => 2,
            self::Competent => 3,
            self::Advanced => 4,
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Beginner => 'neutral',
            self::Developing => 'info',
            self::Competent => 'success',
            self::Advanced => 'warning',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $level): array => [$level->value => $level->label()])
            ->all();
    }
}
