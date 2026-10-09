<?php

namespace App\Enums;

/**
 * Difficulty of a free course.
 */
enum CourseLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Beginner => 'green',
            self::Intermediate => 'amber',
            self::Advanced => 'red',
        };
    }
}
